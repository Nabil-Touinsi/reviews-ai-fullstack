# Echo Reviews  AI‑Powered Review Intelligence Platform

Echo Reviews is a **full‑stack AI application** that allows organizations to **analyze, store, and monitor user reviews** in real time.  
It combines **NLP sentiment analysis**, **topic extraction**, and a **modern analytics dashboard** to transform raw feedback into actionable insights.

---

## ✨ Key Features

### 🔍 Review Analysis (AI)
- Automatic **sentiment detection** (positive / neutral / negative)
- **Score normalization** (0–100)
- **Keyword & topic extraction**
- Transparent & explainable AI outputs

### 📊 Interactive Dashboard
- Global KPIs (total reviews, average score)
- Sentiment distribution with visual indicators
- Dominant trend detection
- Top topics & latest reviews
- Real‑time refresh

### 👥 Role‑Based Access
- **User**: analyze & view personal data
- **Admin**: access global statistics & moderation tools
- Secure authentication via **Laravel Sanctum**

### 🛠️ Modern Tech Stack
- **Backend**: Laravel 10 (REST API)
- **Frontend**: Vue 3 + Vite
- **Auth**: Sanctum (token‑based)
- **Database**: MySQL / MariaDB
- **Architecture**: Clean, scalable, production‑ready

---

## 🧱 Project Architecture

```
reviews-ai-fullstack/
│
├── reviews-ai-api/        # Laravel REST API
│   ├── app/
│   │   ├── Http/Controllers
│   │   ├── Models
│   │   ├── Services
│   │   └── Middleware
│   ├── routes/api.php
│   ├── database/
│   └── .env.example
│
├── reviews-ai-front/      # Vue 3 Frontend (Vite)
│   ├── src/
│   │   ├── views/
│   │   ├── components/
│   │   ├── services/
│   │   └── router/
│   └── vite.config.js
│
└── README.md
```

---

## 🚀 Getting Started

### 1️⃣ Prerequisites
- PHP >= 8.1
- Composer
- Node.js >= 18
- MySQL
- Git

---

## 🔧 Backend Setup (Laravel API)

```bash
cd reviews-ai-api
composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env`:
```env
DB_DATABASE=reviews_ai
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:
```bash
php artisan migrate
```

Start API:
```bash
php artisan serve
```

API available at:  
👉 `http://localhost:8000/api`

---

## 🎨 Frontend Setup (Vue 3)

```bash
cd reviews-ai-front
npm install
npm run dev
```

Frontend available at:  
👉 `http://localhost:5173`

---

## 🔐 Authentication Flow

1. User registers / logs in
2. API returns a **Sanctum token**
3. Token stored in `localStorage`
4. Token sent via `Authorization: Bearer <token>`
5. Role‑based access enforced server‑side

---

## 📡 Main API Endpoints

### Public
- `POST /api/register`
- `POST /api/login`
- `POST /api/analyze`

### Authenticated
- `GET /api/dashboard`
- `GET /api/reviews`
- `POST /api/reviews`

### Admin only
- `GET /api/admin/stats`
- `DELETE /api/reviews/{id}`

---

## 🧪 Sample Data

To populate the dashboard:
1. Go to **Analyser**
2. Submit several text reviews
3. Save them
4. Dashboard auto‑updates

---

## 🧠 AI & Explainability

Echo Reviews emphasizes **interpretable AI**:
- Keywords detected are exposed
- Topics are explicit
- No black‑box scoring

This makes the platform suitable for:
- Business intelligence
- Academic projects
- Demonstrations & MVPs

---

## 📈 Roadmap

- 📊 Advanced charts (time‑series, trends)
- 🌍 Multilingual NLP
- 📤 Export (CSV / PDF)
- 🧠 Model fine‑tuning
- ☁️ Cloud deployment (Docker)

---

## 🛡️ Security & Best Practices

- Token‑based auth (Sanctum)
- Role middleware (`admin`)
- Clean separation Front / API
- Environment‑based config

---

## 📄 License

This project is provided for **educational and demonstration purposes**.  
For commercial usage, adaptation or resale, please contact the author.

---

## 👨‍💻 Author
**Nabil Touinsi** **Celia Lamari** **Yanis Skalli**

**Echo Reviews**  
Designed & built as a full‑stack AI product with production standards.

---

> _From raw opinions to clear decisions — Echo Reviews._

