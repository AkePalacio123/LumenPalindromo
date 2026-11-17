const express = require('express');
const cors = require('cors');
require('dotenv').config();
const { OpenAI } = require('openai');
const mysql = require('mysql2/promise');

const app = express();
app.use(express.json());
app.use(cors());

const openrouter = process.env.OPENROUTER_API_KEY
  ? new OpenAI({
      baseURL: 'https://openrouter.ai/api/v1',
      apiKey: process.env.OPENROUTER_API_KEY,
      defaultHeaders: {
        'HTTP-Referer': 'https://tu-sitio-web.com',
        'X-Title': 'Lumen Palindromo',
      },
    })
  : null;

const sakila = mysql.createPool({
  host: process.env.SAKILA_DB_HOST || '127.0.0.1',
  port: Number(process.env.SAKILA_DB_PORT || 3306),
  database: process.env.SAKILA_DB_NAME || 'sakila',
  user: process.env.SAKILA_DB_USER || 'root',
  password: process.env.SAKILA_DB_PASSWORD || '',
  waitForConnections: true,
  connectionLimit: 5,
});

const queryTool = {
  type: 'function',
  function: {
    name: 'query_sakila',
    description: 'Ejecuta una sola consulta SQL SELECT de solo lectura en la base Sakila y devuelve sus filas.',
    parameters: {
      type: 'object',
      properties: {
        sql: {
          type: 'string',
          description: 'Una sola sentencia SELECT para consultar tablas o vistas de Sakila.',
        },
      },
      required: ['sql'],
      additionalProperties: false,
    },
  },
};

const systemMessage = {
  role: 'system',
  content: [
    'Eres un agente analítico que responde en español y utiliza la base de datos Sakila.',
    'Para responder preguntas sobre películas, clientes, alquileres, tiendas o ingresos, consulta siempre Sakila con la herramienta query_sakila; no inventes datos.',
    'Puedes consultar las tablas film, category, film_category, inventory, rental, customer, payment, store y staff, y otras tablas Sakila si hacen falta.',
    'Usa consultas SELECT sencillas, de solo lectura. Para recomendar películas de acción largas y populares, cruza categoría, duración, inventario y alquileres y ordena por alquileres.',
    'Para riesgo de abandono, calcula días desde el alquiler más reciente y explica el umbral usado; advierte si los datos son históricos y no permiten inferir el comportamiento actual.',
    'Para movimiento semanal, cuenta alquileres por día de la semana y calcula ingresos promedio por día calendario usando payment.amount y payment.payment_date.',
    'Resume resultados concretos, indica las métricas y explica cualquier limitación de los datos.',
  ].join(' '),
};

function validateReadOnlySelect(sql) {
  if (typeof sql !== 'string' || !sql.trim()) {
    throw new Error('La consulta SQL está vacía.');
  }

  const statement = sql.trim().replace(/;+\s*$/, '');
  if (!/^SELECT\b/i.test(statement) || /[;`]/.test(statement)) {
    throw new Error('Solo se permiten consultas SELECT de una sentencia.');
  }

  if (/--|\/\*|\*\/|#|\b(?:INSERT|UPDATE|DELETE|REPLACE|DROP|ALTER|CREATE|TRUNCATE|GRANT|REVOKE|CALL|EXECUTE|LOAD_FILE|SLEEP|BENCHMARK|GET_LOCK)\b|\bINTO\s+(?:OUTFILE|DUMPFILE)\b/i.test(statement)) {
    throw new Error('La consulta contiene una operación o elemento no permitido.');
  }

  const requestedLimit = statement.match(/\bLIMIT\s+(\d+)\s*$/i);
  if (requestedLimit && Number(requestedLimit[1]) > 200) {
    throw new Error('El límite máximo de filas es 200.');
  }

  return requestedLimit ? statement : `${statement} LIMIT 200`;
}

async function runSakilaQuery(sql) {
  const safeSql = validateReadOnlySelect(sql);
  const [rows] = await sakila.query(safeSql);
  return rows;
}

app.post('/api/chat', async (req, res) => {
  try {
    if (!openrouter) {
      throw new Error('Configura OPENROUTER_API_KEY en ai-backend/.env');
    }

    const { message, history } = req.body;
    if (typeof message !== 'string' || !message.trim()) {
      return res.status(400).json({ error: 'Escribe un mensaje para el agente.' });
    }

    const priorMessages = Array.isArray(history)
      ? history
          .filter((item) => item && ['user', 'assistant'].includes(item.role) && typeof item.content === 'string')
          .slice(-12)
      : [];
    const messages = [
      systemMessage,
      ...priorMessages,
      { role: 'user', content: message.trim() },
    ];

    for (let turn = 0; turn < 6; turn += 1) {
      const completion = await openrouter.chat.completions.create({
        model: 'openai/gpt-4o-mini',
        messages,
        tools: [queryTool],
        tool_choice: 'auto',
      });

      const assistantMessage = completion.choices[0].message;
      if (!assistantMessage.tool_calls?.length) {
        return res.json({ reply: assistantMessage.content || 'No pude elaborar una respuesta con los datos disponibles.' });
      }

      messages.push(assistantMessage);

      for (const toolCall of assistantMessage.tool_calls) {
        if (toolCall.function.name !== 'query_sakila') {
          throw new Error('El agente solicitó una herramienta no permitida.');
        }

        const { sql } = JSON.parse(toolCall.function.arguments);
        const rows = await runSakilaQuery(sql);
        messages.push({
          role: 'tool',
          tool_call_id: toolCall.id,
          content: JSON.stringify(rows),
        });
      }
    }

    throw new Error('El agente excedió el número máximo de consultas.');
  } catch (error) {
    console.error('Error al procesar la consulta del agente:', error.message);
    res.status(500).json({ error: 'Error interno al consultar Sakila con la IA' });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Servidor corriendo en puerto ${PORT}`);
});
