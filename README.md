# Healthcare Appointment Management System

A multilingual (English/Sinhala) web-based healthcare appointment management platform with an AI-powered chatbot assistant. Built with PHP and MySQL, supporting three user roles: **Patients**, **Doctors**, and **Administrators**.

## Features

### Patient Portal
- Register and manage profile
- Browse doctors by department/specialty
- Book, view, and manage appointments
- AI healthcare chatbot assistant with:
  - Bilingual support (English & Sinhala/Singlish)
  - Mental health risk assessment & crisis detection
  - Medicine image recognition (vision-capable AI)
  - Doctor discovery & appointment booking via conversation
  - Patient memory with consent management

### Doctor Portal
- View today's appointment schedule
- Track pending and upcoming appointments
- Role-based dashboard with real-time statistics

### Admin Panel
- Full CRUD for appointments, patients, doctors, departments
- Email communication via PHPMailer
- AI provider configuration (OpenAI, Ollama, OpenAI-compatible, Dify)
- Live AI model fetching from configured providers
- System settings & hospital profile management
- Dashboard with key metrics and quick actions

### AI Chatbot
- Multi-provider LLM support with automatic fallback
- Pattern-matching engine for common healthcare intents
- Risk assessment engine for mental health escalation
- Conversation history logging
- Customizable system prompts

## Tech Stack

| Layer        | Technology                                |
|--------------|-------------------------------------------|
| Backend      | PHP 8.2+ (vanilla, no framework)          |
| Database     | MySQL / MariaDB                           |
| Frontend     | HTML5, CSS3, Vanilla JavaScript           |
| Icons        | Font Awesome 7                            |
| AI/LLM       | OpenAI, Ollama, OpenAI-compatible, Dify   |
| Email        | PHPMailer 6+                              |

## Requirements

- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache / Nginx / Laragon)
- cURL extension enabled
- Composer (for PHPMailer dependencies)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/healthcare-appointment-system.git
   ```

2. **Import the database**
   - Create a database named `edoctor`
   - Import `edoctor.sql`:
   ```bash
   mysql -u root -p edoctor < edoctor.sql
   ```

3. **Configure database connection**
   - Update credentials in `config/db_connection.php` if needed
   - Default: `localhost` / `root` / no password

4. **Set up environment variables**
   - Copy `.env.example` to `.env` (or edit existing `.env`)
   - Configure your preferred LLM provider(s):
     - **OpenAI**: Set `OPENAI_API_KEY` and `OPENAI_MODEL`
     - **Ollama** (local): Set `OLLAMA_API_URL` and `OLLAMA_MODEL`
     - **OpenAI-compatible**: Set `OPENAI_COMPATIBLE_API_KEY`, `BASE_URL`, `MODEL`
     - **Dify**: Set `DIFY_API_KEY` and `DIFY_API_URL`
   - Set `LLM_PROVIDER` to your primary provider key

5. **Install dependencies**
   ```bash
   cd admin/phpmailer
   composer install
   ```

6. **Serve the application**
   - Point your web server to the project root
   - Or use Laragon's built-in server

7. **Initialize AI providers** (optional)
   - Visit `/admin/install_ai_providers.php` once to seed the `ai_provider_config` table

## Usage

### Login Credentials

- **Patient**: Register via `/auth/register.php`
- **Doctor**: Pre-seeded doctor accounts (viewable in admin panel)
- **Admin**: Pre-seeded admin account in `users` table

The login page (`/auth/login.php`) displays demo credentials for each role.

### Workflow

1. Patients register and browse doctors by department
2. Appointments are booked with date/time selection
3. Doctors view their schedule on the dashboard
4. Admin manages all entities and sends email reminders
5. The AI chatbot assists patients with queries, booking, and mental health support

## Project Structure

```
├── admin/                 # Admin panel (CRUD, mail, settings, API handlers)
│   ├── api/               # AJAX endpoint handlers
│   ├── phpmailer/         # PHPMailer library
│   └── static/            # Admin CSS/JS assets
├── auth/                  # Login, registration, logout
├── config/                # DB connection, LLM config, OpenAI handler
├── doctor/                # Doctor portal
│   └── static/            # Doctor dashboard assets
├── image/                 # Static media assets
├── patient/               # Patient portal
│   ├── MentalAI/          # AI chatbot agent framework
│   ���   └── agent/         # AgentOrchestrator, RiskEngine, SafetyPolicy, etc.
│   └── static/            # Patient CSS/JS assets
├── static/                # Shared frontend assets
├── .env                   # Environment configuration
├── edoctor.sql            # Database schema & seed data
└── index.php              # Entry point / landing page
```

## AI Chatbot Architecture

The chatbot (`patient/MentalAI/`) uses a multi-agent architecture:

| Component            | Responsibility                                |
|----------------------|-----------------------------------------------|
| `AgentOrchestrator`  | Core logic: risk assessment, DB context, AI fallback, action execution |
| `RiskEngine`         | Keyword-based mental health risk triage       |
| `SafetyPolicy`       | Safety-aware prompt construction & crisis response |
| `ResponseEngine`     | Pattern-matching for common intents           |
| `DoctorDirectory`    | Database queries for doctor discovery         |
| `MemoryStore`        | Patient memory with consent & expiry          |
| `ConversationLogger` | Conversation & event logging                  |

## Database Schema

- **users** — Unified login (patient, doctor, admin)
- **patients** — Patient profiles
- **doctors** — Doctor profiles linked to departments
- **departments** — Medical specialties
- **appointments** — Appointment records with status tracking
- **chat_history** — AI chatbot conversation logs
- **patient_memory** — Patient-specific memory with consent
- **mental_health_events** — Risk escalation events
- **ai_provider_config** — AI provider settings

## License

Proprietary. All rights reserved.

## Support

For issues, feature requests, or contributions, please contact the project maintainer or open an issue on the repository.
