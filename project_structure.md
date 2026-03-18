# Структура проекта
## Семейный финансовый планировщик «Резерв»

**Стек:** Laravel 11 (бэкенд) + Vue 3 (фронтенд)  
**Версия:** 1.0

---

## Монорепозиторий

```
rezerv/
├── backend/          # Laravel 11 — REST API
├── frontend/         # Vue 3 — SPA
├── .github/
│   └── workflows/
│       └── deploy.yml   # CI/CD — автодеплой на Timeweb
└── README.md
```

---

## Backend (Laravel 11)

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── BudgetController.php
│   │   │       ├── MemberController.php
│   │   │       ├── MonthController.php
│   │   │       ├── IncomeController.php
│   │   │       ├── OepController.php
│   │   │       ├── PpController.php
│   │   │       ├── SavingController.php
│   │   │       ├── Operations/
│   │   │       │   ├── IncomeOperationController.php
│   │   │       │   ├── OepOperationController.php
│   │   │       │   ├── PpOperationController.php
│   │   │       │   └── SavingOperationController.php
│   │   │       ├── MonthCloseController.php
│   │   │       ├── HistoryController.php
│   │   │       └── ActivityController.php
│   │   ├── Middleware/
│   │   │   └── BudgetAccess.php      # Проверка доступа к бюджету
│   │   └── Requests/                 # Form Request валидация
│   │       ├── LoginRequest.php
│   │       ├── StoreIncomeRequest.php
│   │       ├── StoreOepRequest.php
│   │       ├── StorePpRequest.php
│   │       ├── StoreSavingRequest.php
│   │       └── CloseMonthRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Budget.php
│   │   ├── Month.php
│   │   ├── Income.php
│   │   ├── MonthIncome.php
│   │   ├── ExpenseOep.php
│   │   ├── MonthOep.php
│   │   ├── ExpensePp.php
│   │   ├── MonthPp.php
│   │   ├── Saving.php
│   │   ├── MonthSaving.php
│   │   └── ActivityLog.php
│   ├── Services/                     # Бизнес-логика (не в контроллерах!)
│   │   ├── MonthService.php          # Генерация месяца, пересчёт показателей
│   │   ├── MonthCloseService.php     # Закрытие месяца
│   │   ├── OperationService.php      # Подтверждение / отмена операций
│   │   ├── RecalculationService.php  # Пересчёт ПП и накоплений при отклонении
│   │   └── ActivityLogService.php    # Запись в лог
│   ├── Enums/
│   │   ├── MonthStatus.php           # active | closed
│   │   ├── IncomeType.php            # permanent | temporary | one_time
│   │   ├── IncomeStatus.php          # planned | confirmed
│   │   ├── OepType.php               # permanent | temporary
│   │   ├── PaymentStatus.php         # planned | written_off | cancelled
│   │   ├── SavingStatus.php          # planned | reserved | written_off | cancelled
│   │   ├── SavingMode.php            # asap | by_date | no_goal
│   │   └── UserRole.php              # owner | member
│   └── Exceptions/
│       ├── InsufficientFreeBalanceException.php
│       ├── MonthAlreadyClosedException.php
│       └── CannotCloseMonthException.php
├── database/
│   ├── migrations/
│   │   ├── 0001_create_users_table.php
│   │   ├── 0002_create_budgets_table.php
│   │   ├── 0003_create_budget_user_table.php
│   │   ├── 0004_create_months_table.php
│   │   ├── 0005_create_incomes_table.php
│   │   ├── 0006_create_month_incomes_table.php
│   │   ├── 0007_create_expenses_oep_table.php
│   │   ├── 0008_create_month_oep_table.php
│   │   ├── 0009_create_expenses_pp_table.php
│   │   ├── 0010_create_month_pp_table.php
│   │   ├── 0011_create_savings_table.php
│   │   ├── 0012_create_month_savings_table.php
│   │   └── 0013_create_activity_log_table.php
│   └── seeders/
│       └── DatabaseSeeder.php        # Тестовые данные для разработки
├── routes/
│   └── api.php                       # Все API-маршруты
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   └── LoginTest.php
│   │   ├── Month/
│   │   │   ├── MonthCloseTest.php
│   │   │   └── OperationsTest.php
│   │   └── Savings/
│   │       └── SavingRecalculationTest.php
│   └── Unit/
│       └── Services/
│           └── MonthServiceTest.php
├── .env.example
└── composer.json
```

---

## Frontend (Vue 3)

```
frontend/
├── src/
│   ├── main.js                   # Точка входа
│   ├── App.vue
│   ├── router/
│   │   └── index.js              # Vue Router — маршруты SPA
│   ├── stores/                   # Pinia — стейт-менеджмент
│   │   ├── auth.js               # Пользователь, токен
│   │   ├── budget.js             # Текущий бюджет
│   │   ├── months.js             # Список месяцев
│   │   └── activity.js           # Лог действий
│   ├── api/                      # Обёртки над axios
│   │   ├── client.js             # Настройка axios (base URL, headers)
│   │   ├── auth.js
│   │   ├── budget.js
│   │   ├── months.js
│   │   ├── incomes.js
│   │   ├── oep.js
│   │   ├── pp.js
│   │   ├── savings.js
│   │   ├── operations.js
│   │   └── history.js
│   ├── views/                    # Страницы (роуты)
│   │   ├── LoginView.vue
│   │   ├── DashboardView.vue     # Главная — таблица месяцев
│   │   ├── MonthDetailView.vue   # Детали одного месяца
│   │   ├── HistoryView.vue       # История закрытых месяцев
│   │   ├── SettingsView.vue      # Управление статьями (ОЕП, ПП, накопления)
│   │   └── ActivityView.vue      # Лог действий
│   ├── components/
│   │   ├── layout/
│   │   │   ├── AppHeader.vue
│   │   │   └── AppSidebar.vue
│   │   ├── dashboard/
│   │   │   ├── MonthsTable.vue   # Главная таблица: строки × колонки
│   │   │   ├── MonthColumn.vue   # Одна колонка месяца
│   │   │   ├── BalanceSummary.vue # ВС / Резерв / Свободный остаток
│   │   │   └── ActivityFeed.vue  # Лог последних 50 действий
│   │   ├── operations/
│   │   │   ├── PaymentRow.vue    # Строка платежа с действиями
│   │   │   ├── IncomeRow.vue     # Строка дохода с действиями
│   │   │   ├── ConfirmDialog.vue # Диалог подтверждения суммы
│   │   │   └── RecalcDialog.vue  # Диалог выбора стратегии пересчёта
│   │   ├── month/
│   │   │   ├── CloseMonthPanel.vue  # Панель закрытия месяца
│   │   │   └── CloseMonthCheck.vue  # Чеклист перед закрытием
│   │   ├── settings/
│   │   │   ├── OepList.vue
│   │   │   ├── PpList.vue
│   │   │   ├── SavingsList.vue
│   │   │   ├── IncomesList.vue
│   │   │   └── forms/
│   │   │       ├── OepForm.vue
│   │   │       ├── PpForm.vue
│   │   │       ├── SavingForm.vue
│   │   │       └── IncomeForm.vue
│   │   └── ui/                   # Переиспользуемые UI-компоненты
│   │       ├── BaseButton.vue
│   │       ├── BaseInput.vue
│   │       ├── BaseModal.vue
│   │       ├── BaseTable.vue
│   │       ├── StatusBadge.vue   # Запланирован / Списан / Отменён
│   │       └── AmountDisplay.vue # Форматирование сумм (1 000,00 ₽)
│   └── utils/
│       ├── formatters.js         # Форматирование дат и сумм
│       └── constants.js          # Статусы, режимы накоплений
├── public/
├── index.html
├── vite.config.js
└── package.json
```

---

## GitHub Actions — CI/CD

```yaml
# .github/workflows/deploy.yml
name: Deploy to Timeweb

on:
  push:
    branches: [main]

jobs:
  deploy-backend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Deploy Laravel to Timeweb
        # SSH → composer install → php artisan migrate → restart

  deploy-frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Build Vue
        run: cd frontend && npm install && npm run build
      - name: Upload dist to Timeweb
        # SCP dist/ → public_html/
```

---

## Порядок разработки (рекомендуемый)

```
Этап 1 — Backend основа
  ├── Миграции БД (все 13 таблиц)
  ├── Модели + связи (Eloquent)
  ├── Аутентификация (Sanctum)
  └── Базовые CRUD: доходы, ОЕП, ПП, накопления

Этап 2 — Бизнес-логика Backend
  ├── MonthService — генерация месяца из шаблонов
  ├── OperationService — подтверждение / отмена / откат
  ├── RecalculationService — пересчёт при отклонениях
  └── MonthCloseService — закрытие месяца

Этап 3 — Frontend основа
  ├── Аутентификация (LoginView + Pinia auth store)
  ├── API-клиент (axios + все модули)
  └── DashboardView — таблица месяцев

Этап 4 — Frontend операции
  ├── Подтверждение / отмена платежей
  ├── Диалоги пересчёта
  └── Закрытие месяца

Этап 5 — Полировка
  ├── История и лог
  ├── Настройки статей
  └── CI/CD деплой
```
