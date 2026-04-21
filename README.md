# Emfitpro - IA Fitness Coach 🏋️‍♂️🤖

![Emfitpro Banner](https://raw.githubusercontent.com/marketingdigitalcesar-cpu/emfitpro/main/assets/hero-home.png)

Emfitpro es una aplicación web (PWA) de última generación que utiliza Inteligencia Artificial para democratizar el acceso a entrenamiento personalizado de alto nivel.

## 🚀 Características Principales

- **Coach IA Multidisciplinario**: Acceso a Entrenador, Nutricionista y Psicólogo deportivo impulsados por modelos de lenguaje avanzados.
- **Generador de Rutinas Dinámico**: Rutinas personalizadas basadas en tu equipo disponible, tiempo y objetivos.
- **Pasarela de Pagos**: Integración completa con **Wompi (Bancolombia)** para suscripcionesPRO.
- **Comunidad Activa**: Comparte tus logros y entrenamientos con otros usuarios.
- **Google Login**: Autenticación rápida y segura.
- **PWA Ready**: Instálala en tu móvil como una aplicación nativa.

## 🛠️ Stack Tecnológico

- **Backend**: PHP 8.x
- **Base de Datos**: MySQL / MariaDB
- **Automatización**: n8n (Webhooks & AI Agents)
- **Frontend**: Vanilla JS, Modern CSS (Glassmorphism design)
- **Despliegue**: Docker & Easypanel

## ⚙️ Configuración del Entorno

Para ejecutar este proyecto localmente o en producción, debes configurar las siguientes variables de entorno. Puedes usar el archivo `.env.example` como referencia.

### Variables Requeridas:

| Variable | Descripción |
|----------|-------------|
| `DB_HOST` | Host de la base de datos |
| `DB_NAME` | Nombre de la base de datos |
| `DB_USER` | Usuario de la base de datos |
| `DB_PASS` | Contraseña de la base de datos |
| `OPENAI_API_KEY` | Tu API Key de OpenAI |
| `GOOGLE_CLIENT_ID` | Client ID de Google Cloud |
| `GOOGLE_CLIENT_SECRET` | Client Secret de Google Cloud |
| `WOMPI_PUBLIC_KEY` | Llave pública de Wompi |
| `WOMPI_INTEGRITY_SECRET` | Secreto de integridad de Wompi |

## 📦 Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/marketingdigitalcesar-cpu/emfitpro.git
   ```
2. Importa el archivo `database.sql` en tu servidor MySQL.
3. Configura tus variables de entorno en tu servidor o crea un archivo `.env` local.
4. ¡Listo! Abre la aplicación en tu navegador.

## 🤝 Contribuciones

Si deseas mejorar Emfitpro, ¡eres bienvenido!
1. Haz un Fork del proyecto.
2. Crea una rama para tu mejora (`git checkout -b feature/MejoraIncreible`).
3. Haz un commit con tus cambios (`git commit -m 'feat: Agregada nueva funcionalidad'`).
4. Haz un Push a la rama (`git push origin feature/MejoraIncreible`).
5. Abre un Pull Request.

---
Desarrollado con ❤️ por **Cesar - Marketing Digital CPU**
