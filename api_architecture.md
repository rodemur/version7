# Архитектура API
## Семейный финансовый планировщик «Резерв»

**Стек:** Laravel 11 · REST API · Laravel Sanctum  
**Версия:** 1.0  
**Базовый URL:** `https://your-domain.com/api/v1`

---

## Содержание

1. [Общие принципы](#1-общие-принципы)
2. [Аутентификация](#2-аутентификация)
3. [Бюджет и пользователи](#3-бюджет-и-пользователи)
4. [Месяцы](#4-месяцы)
5. [Доходы](#5-доходы)
6. [ОЕП — Обязательные ежемесячные платежи](#6-оеп--обязательные-ежемесячные-платежи)
7. [ПП — Плановые платежи](#7-пп--плановые-платежи)
8. [Накопления](#8-накопления)
9. [Операции месяца (подтверждение)](#9-операции-месяца-подтверждение)
10. [Закрытие месяца](#10-закрытие-месяца)
11. [История и лог](#11-история-и-лог)
12. [Формат ответов и ошибок](#12-формат-ответов-и-ошибок)

---

## 1. Общие принципы

### Аутентификация
Все эндпоинты (кроме `/auth/*`) требуют заголовок:
```
Authorization: Bearer {token}
```

### Версионирование
Все маршруты имеют префикс `/api/v1/`.

### Формат данных
- Запросы и ответы: `Content-Type: application/json`
- Суммы: `decimal` с двумя знаками после запятой (`"amount": "45000.00"`)
- Даты: `ISO 8601` (`"2025-03-01"`)
- Статусы HTTP:

| Код | Значение |
|---|---|
| 200 | Успешный запрос |
| 201 | Ресурс создан |
| 204 | Успешно, без тела ответа |
| 400 | Ошибка валидации |
| 401 | Не авторизован |
| 403 | Нет доступа |
| 404 | Не найдено |
| 409 | Конфликт (например, месяц уже закрыт) |
| 422 | Бизнес-логика не позволяет выполнить операцию |
| 500 | Ошибка сервера |

---

## 2. Аутентификация

### `POST /api/v1/auth/login`
Вход по email и паролю. Возвращает токен.

**Запрос:**
```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

**Ответ `200`:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Иван",
    "email": "user@example.com"
  }
}
```

---

### `POST /api/v1/auth/logout`
Выход. Удаляет текущий токен.

**Ответ `204`:** _(пустое тело)_

---

### `GET /api/v1/auth/me`
Текущий пользователь.

**Ответ `200`:**
```json
{
  "id": 1,
  "name": "Иван",
  "email": "user@example.com",
  "budgets": [
    { "id": 1, "name": "Семейный бюджет", "role": "owner" }
  ]
}
```

---

## 3. Бюджет и пользователи

### `GET /api/v1/budgets/{budget_id}`
Информация о бюджете.

**Ответ `200`:**
```json
{
  "id": 1,
  "name": "Семейный бюджет",
  "owner_id": 1,
  "members": [
    { "id": 1, "name": "Иван", "role": "owner" },
    { "id": 2, "name": "Мария", "role": "member" }
  ]
}
```

---

### `DELETE /api/v1/budgets/{budget_id}`
Удаление бюджета. **Только Владелец.**

**Ответ `204`**

---

### `POST /api/v1/budgets/{budget_id}/members`
Добавление участника. **Только Владелец.**

**Запрос:**
```json
{
  "user_id": 2
}
```

**Ответ `201`:**
```json
{
  "user_id": 2,
  "role": "member"
}
```

---

### `DELETE /api/v1/budgets/{budget_id}/members/{user_id}`
Удаление участника. **Только Владелец.**

**Ответ `204`**

---

## 4. Месяцы

### `GET /api/v1/budgets/{budget_id}/months`
Список месяцев для дашборда: 3 закрытых + текущий + 12 будущих.

**Ответ `200`:**
```json
{
  "months": [
    {
      "id": 10,
      "period": "2025-01-01",
      "status": "closed",
      "virtual_account": "85000.00",
      "reserved_funds": "32000.00",
      "free_balance": "53000.00",
      "carried_over_balance": "5000.00"
    },
    {
      "id": 13,
      "period": "2025-04-01",
      "status": "active",
      "virtual_account": "91000.00",
      "reserved_funds": "35000.00",
      "free_balance": "56000.00",
      "carried_over_balance": "6000.00",
      "planned": {
        "income": "130000.00",
        "oep": "63000.00",
        "pp": "4000.00",
        "savings": "20000.00",
        "free_balance": "43000.00"
      }
    }
  ]
}
```

---

### `GET /api/v1/budgets/{budget_id}/months/{month_id}`
Детальные данные одного месяца — весь дашборд.

**Ответ `200`:**
```json
{
  "id": 13,
  "period": "2025-04-01",
  "status": "active",
  "virtual_account": "91000.00",
  "reserved_funds": "35000.00",
  "free_balance": "56000.00",
  "planned_free_balance": "43000.00",
  "carried_over_balance": "6000.00",
  "incomes": [ "...см. раздел 5..." ],
  "oep": [ "...см. раздел 6..." ],
  "pp": [ "...см. раздел 7..." ],
  "savings": [ "...см. раздел 8..." ],
  "totals": {
    "planned_income": "130000.00",
    "planned_oep": "63000.00",
    "planned_pp": "4000.00",
    "planned_savings": "20000.00",
    "actual_income": "65000.00",
    "actual_oep": "45000.00",
    "actual_pp": "2000.00",
    "actual_savings": "10000.00"
  }
}
```

---

## 5. Доходы

### `GET /api/v1/budgets/{budget_id}/incomes`
Список шаблонов доходов бюджета.

**Ответ `200`:**
```json
{
  "incomes": [
    {
      "id": 1,
      "name": "Зарплата",
      "type": "permanent",
      "amount": "120000.00",
      "is_paused": false,
      "user": { "id": 1, "name": "Иван" }
    }
  ]
}
```

---

### `POST /api/v1/budgets/{budget_id}/incomes`
Создание нового дохода (бессрочного или временного).

**Запрос:**
```json
{
  "name": "Проектный контракт",
  "type": "temporary",
  "amount": "50000.00",
  "starts_at": "2025-03-01",
  "ends_at": "2025-08-31"
}
```

**Ответ `201`:** _(созданный объект)_

---

### `PUT /api/v1/budgets/{budget_id}/incomes/{income_id}`
Редактирование шаблона дохода.

**Запрос:**
```json
{
  "amount": "125000.00",
  "apply_from": "2025-05-01"
}
```

---

### `PATCH /api/v1/budgets/{budget_id}/incomes/{income_id}/pause`
Приостановка / возобновление дохода.

**Запрос:**
```json
{
  "is_paused": true
}
```

---

### `DELETE /api/v1/budgets/{budget_id}/incomes/{income_id}`
Удаление шаблона дохода.

**Ответ `204`**

---

### `POST /api/v1/budgets/{budget_id}/months/{month_id}/incomes`
Добавление разового дохода в текущий месяц.

**Запрос:**
```json
{
  "name": "Продажа велосипеда",
  "amount": "15000.00"
}
```

**Ответ `201`:** _(созданная запись `month_incomes`)_

---

## 6. ОЕП — Обязательные ежемесячные платежи

### `GET /api/v1/budgets/{budget_id}/oep`
Список шаблонов ОЕП.

**Ответ `200`:**
```json
{
  "oep": [
    {
      "id": 1,
      "name": "Аренда квартиры",
      "type": "permanent",
      "amount": "45000.00",
      "is_active": true
    },
    {
      "id": 2,
      "name": "Кредит на авто",
      "type": "temporary",
      "amount": "18000.00",
      "starts_at": "2024-01-01",
      "ends_at": "2026-12-31",
      "is_active": true
    }
  ]
}
```

---

### `POST /api/v1/budgets/{budget_id}/oep`
Создание статьи ОЕП.

**Запрос:**
```json
{
  "name": "Аренда квартиры",
  "type": "permanent",
  "amount": "45000.00"
}
```

---

### `PUT /api/v1/budgets/{budget_id}/oep/{oep_id}`
Редактирование статьи ОЕП (постоянное изменение с указанием месяца применения).

**Запрос:**
```json
{
  "amount": "47000.00",
  "apply_from": "2025-05-01"
}
```

---

### `DELETE /api/v1/budgets/{budget_id}/oep/{oep_id}`
Деактивация / удаление статьи ОЕП.

**Ответ `204`**

---

## 7. ПП — Плановые платежи

### `GET /api/v1/budgets/{budget_id}/pp`
Список шаблонов ПП.

**Ответ `200`:**
```json
{
  "pp": [
    {
      "id": 1,
      "name": "ОСАГО",
      "target_amount": "12000.00",
      "monthly_payment": "1000.00",
      "period_months": 12,
      "target_date": "2025-09-01",
      "accumulated": "4000.00",
      "remaining": "8000.00",
      "is_active": true
    }
  ]
}
```

---

### `POST /api/v1/budgets/{budget_id}/pp`
Создание статьи ПП.

**Запрос:**
```json
{
  "name": "ОСАГО",
  "target_amount": "12000.00",
  "period_months": 12,
  "target_date": "2025-09-01"
}
```

**Ответ `201`:** _(созданный объект с рассчитанным `monthly_payment`)_

---

### `PUT /api/v1/budgets/{budget_id}/pp/{pp_id}`
Редактирование статьи ПП.

**Запрос:**
```json
{
  "target_amount": "13000.00",
  "apply_from": "2025-05-01"
}
```

---

### `DELETE /api/v1/budgets/{budget_id}/pp/{pp_id}`
Деактивация статьи ПП.

**Ответ `204`**

---

## 8. Накопления

### `GET /api/v1/budgets/{budget_id}/savings`
Список накоплений.

**Ответ `200`:**
```json
{
  "savings": [
    {
      "id": 1,
      "name": "На отпуск",
      "mode": "by_date",
      "target_amount": "60000.00",
      "monthly_payment": "10000.00",
      "target_date": "2025-06-01",
      "accumulated": "30000.00",
      "remaining": "30000.00",
      "is_active": true
    }
  ]
}
```

---

### `POST /api/v1/budgets/{budget_id}/savings`
Создание накопления.

**Запрос (режим `by_date`):**
```json
{
  "name": "На отпуск",
  "mode": "by_date",
  "target_amount": "60000.00",
  "target_date": "2025-06-01"
}
```

**Запрос (режим `asap`):**
```json
{
  "name": "Подушка безопасности",
  "mode": "asap",
  "target_amount": "100000.00"
}
```

**Запрос (режим `no_goal`):**
```json
{
  "name": "Копилка",
  "mode": "no_goal",
  "monthly_payment": "5000.00"
}
```

**Ответ `201`:** _(созданный объект с рассчитанным `monthly_payment`)_

**Ошибка `422`** — если рассчитанный платёж превышает свободный остаток:
```json
{
  "error": "insufficient_free_balance",
  "message": "Платёж 10 000 ₽ превышает свободный остаток 7 500 ₽",
  "free_balance": "7500.00",
  "required_payment": "10000.00"
}
```

---

### `PUT /api/v1/budgets/{budget_id}/savings/{saving_id}`
Редактирование накопления.

---

### `DELETE /api/v1/budgets/{budget_id}/savings/{saving_id}`
Деактивация накопления.

**Ответ `204`**

---

## 9. Операции месяца (подтверждение)

Все эндпоинты этого раздела изменяют статус конкретной записи в текущем активном месяце и пересчитывают `virtual_account`, `reserved_funds`, `free_balance` в таблице `months`.

---

### Доходы

#### `PATCH /api/v1/months/{month_id}/incomes/{month_income_id}/confirm`
Подтверждение дохода. ВС увеличивается.

**Запрос:**
```json
{
  "actual_amount": "120000.00"
}
```

**Ответ `200`:**
```json
{
  "month_income": {
    "id": 5,
    "status": "confirmed",
    "planned_amount": "120000.00",
    "actual_amount": "120000.00"
  },
  "month_totals": {
    "virtual_account": "211000.00",
    "reserved_funds": "35000.00",
    "free_balance": "176000.00"
  }
}
```

---

#### `PATCH /api/v1/months/{month_id}/incomes/{month_income_id}/reset`
Откат подтверждения (пока месяц не закрыт).

**Ответ `200`:** _(обновлённые totals)_

---

### ОЕП

#### `PATCH /api/v1/months/{month_id}/oep/{month_oep_id}/write-off`
Списание ОЕП. ВС уменьшается на фактическую сумму.

**Запрос:**
```json
{
  "actual_amount": "45000.00"
}
```

**Ответ `200`:**
```json
{
  "month_oep": {
    "id": 3,
    "status": "written_off",
    "planned_amount": "45000.00",
    "actual_amount": "45000.00"
  },
  "month_totals": { "...": "..." }
}
```

---

#### `PATCH /api/v1/months/{month_id}/oep/{month_oep_id}/cancel`
Отмена платежа ОЕП. ВС не изменяется.

**Ответ `200`:** _(обновлённые totals)_

---

#### `PATCH /api/v1/months/{month_id}/oep/{month_oep_id}/reset`
Откат действия (пока месяц не закрыт).

**Ответ `200`:** _(обновлённые totals)_

---

### ПП

#### `PATCH /api/v1/months/{month_id}/pp/{month_pp_id}/write-off`
Подтверждение взноса ПП. Средства резервируются (ВС не меняется).

**Запрос:**
```json
{
  "actual_amount": "1000.00"
}
```

**Если `actual_amount < planned_amount`** — сервер возвращает варианты пересчёта:
```json
{
  "month_pp": { "...": "..." },
  "recalculation_options": {
    "shift_date": {
      "new_target_date": "2025-10-01",
      "description": "Дата цели сдвинется на 1 месяц"
    },
    "increase_payment": {
      "new_monthly_payment": "1111.00",
      "available": true,
      "description": "Платёж увеличится до 1 111 ₽ / мес"
    }
  }
}
```

#### `PATCH /api/v1/months/{month_id}/pp/{month_pp_id}/recalculate`
Применить выбранный вариант пересчёта после отклонения.

**Запрос:**
```json
{
  "strategy": "shift_date"
}
```
или
```json
{
  "strategy": "increase_payment"
}
```

---

#### `PATCH /api/v1/months/{month_id}/pp/{month_pp_id}/spend`
Списание накопленной суммы ПП с ВС (кнопка «Потратить» при достижении цели).

**Ответ `200`:** _(обновлённые totals + новый цикл ПП)_

---

#### `PATCH /api/v1/months/{month_id}/pp/{month_pp_id}/cancel`
Отмена взноса ПП.

#### `PATCH /api/v1/months/{month_id}/pp/{month_pp_id}/reset`
Откат действия.

---

### Накопления

#### `PATCH /api/v1/months/{month_id}/savings/{month_saving_id}/write-off`
Подтверждение взноса. Средства резервируются.

**Запрос / ответ:** аналогично ПП, включая `recalculation_options`.

#### `PATCH /api/v1/months/{month_id}/savings/{month_saving_id}/recalculate`
Применить стратегию пересчёта.

#### `PATCH /api/v1/months/{month_id}/savings/{month_saving_id}/spend`
Кнопка «Потратить» — списать накопленное с ВС.

#### `PATCH /api/v1/months/{month_id}/savings/{month_saving_id}/cancel`
Отмена взноса.

#### `PATCH /api/v1/months/{month_id}/savings/{month_saving_id}/reset`
Откат действия.

---

## 10. Закрытие месяца

### `GET /api/v1/months/{month_id}/close/check`
Предварительная проверка — можно ли закрыть месяц.

**Ответ `200` (всё готово):**
```json
{
  "can_close": true,
  "free_balance": "12000.00",
  "pending_operations": []
}
```

**Ответ `200` (есть проблемы):**
```json
{
  "can_close": false,
  "free_balance": "-3000.00",
  "pending_operations": [
    { "type": "month_oep", "id": 7, "name": "Электричество", "status": "planned" },
    { "type": "month_income", "id": 3, "name": "Зарплата", "status": "planned" }
  ],
  "errors": [
    "Есть неподтверждённые операции: 2",
    "Свободный остаток отрицательный: -3 000 ₽"
  ]
}
```

---

### `POST /api/v1/months/{month_id}/close`
Закрытие месяца.

**Запрос:**
```json
{
  "free_balance_action": "carry_over"
}
```
или
```json
{
  "free_balance_action": "write_off"
}
```

**Ответ `200`:**
```json
{
  "closed_month": {
    "id": 13,
    "period": "2025-04-01",
    "status": "closed",
    "virtual_account": "91000.00",
    "free_balance": "0.00",
    "closed_at": "2025-04-30T18:32:00Z"
  },
  "next_month": {
    "id": 14,
    "period": "2025-05-01",
    "status": "active",
    "carried_over_balance": "12000.00"
  }
}
```

**Ошибка `422`:**
```json
{
  "error": "cannot_close_month",
  "message": "Нельзя закрыть месяц: есть неподтверждённые операции или отрицательный остаток"
}
```

---

## 11. История и лог

### `GET /api/v1/budgets/{budget_id}/history`
Список закрытых месяцев.

**Ответ `200`:**
```json
{
  "months": [
    {
      "id": 10,
      "period": "2025-01-01",
      "status": "closed",
      "virtual_account": "85000.00",
      "free_balance": "5000.00",
      "closed_at": "2025-01-31T20:00:00Z"
    }
  ]
}
```

---

### `GET /api/v1/budgets/{budget_id}/activity`
Лог последних 50 действий.

**Query params:** `?user_id=1&entity_type=month_oep&from=2025-01-01&to=2025-04-30`

**Ответ `200`:**
```json
{
  "activity": [
    {
      "id": 241,
      "user": { "id": 1, "name": "Иван" },
      "action": "write_off",
      "entity_type": "month_oep",
      "entity_id": 7,
      "description": "Списан платёж «Аренда» — 45 000 ₽",
      "created_at": "2025-04-05T10:23:00Z"
    }
  ],
  "total": 50
}
```

---

## 12. Формат ответов и ошибок

### Успешный ответ
```json
{
  "data": { "...": "..." }
}
```
_(или напрямую объект/массив для простых эндпоинтов)_

### Ошибка валидации `400`
```json
{
  "error": "validation_error",
  "message": "Ошибка валидации",
  "errors": {
    "amount": ["Значение должно быть больше нуля"],
    "target_date": ["Дата должна быть в будущем"]
  }
}
```

### Бизнес-ошибка `422`
```json
{
  "error": "insufficient_free_balance",
  "message": "Недостаточно свободного остатка",
  "free_balance": "7500.00",
  "required": "10000.00"
}
```

### Ошибка доступа `403`
```json
{
  "error": "forbidden",
  "message": "Только Владелец может выполнить это действие"
}
```

---

## Сводная таблица эндпоинтов

| Метод | URL | Описание |
|---|---|---|
| POST | `/auth/login` | Вход |
| POST | `/auth/logout` | Выход |
| GET | `/auth/me` | Текущий пользователь |
| GET | `/budgets/{id}` | Данные бюджета |
| DELETE | `/budgets/{id}` | Удалить бюджет |
| POST | `/budgets/{id}/members` | Добавить участника |
| DELETE | `/budgets/{id}/members/{uid}` | Удалить участника |
| GET | `/budgets/{id}/months` | Список месяцев (дашборд) |
| GET | `/budgets/{id}/months/{mid}` | Детали месяца |
| GET | `/budgets/{id}/incomes` | Список доходов |
| POST | `/budgets/{id}/incomes` | Создать доход |
| PUT | `/budgets/{id}/incomes/{iid}` | Изменить доход |
| PATCH | `/budgets/{id}/incomes/{iid}/pause` | Пауза дохода |
| DELETE | `/budgets/{id}/incomes/{iid}` | Удалить доход |
| POST | `/budgets/{id}/months/{mid}/incomes` | Разовый доход в месяц |
| GET | `/budgets/{id}/oep` | Список ОЕП |
| POST | `/budgets/{id}/oep` | Создать ОЕП |
| PUT | `/budgets/{id}/oep/{oid}` | Изменить ОЕП |
| DELETE | `/budgets/{id}/oep/{oid}` | Удалить ОЕП |
| GET | `/budgets/{id}/pp` | Список ПП |
| POST | `/budgets/{id}/pp` | Создать ПП |
| PUT | `/budgets/{id}/pp/{pid}` | Изменить ПП |
| DELETE | `/budgets/{id}/pp/{pid}` | Удалить ПП |
| GET | `/budgets/{id}/savings` | Список накоплений |
| POST | `/budgets/{id}/savings` | Создать накопление |
| PUT | `/budgets/{id}/savings/{sid}` | Изменить накопление |
| DELETE | `/budgets/{id}/savings/{sid}` | Удалить накопление |
| PATCH | `/months/{mid}/incomes/{id}/confirm` | Подтвердить доход |
| PATCH | `/months/{mid}/incomes/{id}/reset` | Откатить доход |
| PATCH | `/months/{mid}/oep/{id}/write-off` | Списать ОЕП |
| PATCH | `/months/{mid}/oep/{id}/cancel` | Отменить ОЕП |
| PATCH | `/months/{mid}/oep/{id}/reset` | Откатить ОЕП |
| PATCH | `/months/{mid}/pp/{id}/write-off` | Подтвердить взнос ПП |
| PATCH | `/months/{mid}/pp/{id}/recalculate` | Пересчитать ПП |
| PATCH | `/months/{mid}/pp/{id}/spend` | Потратить ПП |
| PATCH | `/months/{mid}/pp/{id}/cancel` | Отменить ПП |
| PATCH | `/months/{mid}/pp/{id}/reset` | Откатить ПП |
| PATCH | `/months/{mid}/savings/{id}/write-off` | Подтвердить взнос накопления |
| PATCH | `/months/{mid}/savings/{id}/recalculate` | Пересчитать накопление |
| PATCH | `/months/{mid}/savings/{id}/spend` | Потратить накопление |
| PATCH | `/months/{mid}/savings/{id}/cancel` | Отменить накопление |
| PATCH | `/months/{mid}/savings/{id}/reset` | Откатить накопление |
| GET | `/months/{mid}/close/check` | Проверить готовность к закрытию |
| POST | `/months/{mid}/close` | Закрыть месяц |
| GET | `/budgets/{id}/history` | История закрытых месяцев |
| GET | `/budgets/{id}/activity` | Лог действий |
