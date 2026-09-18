#### App Расписание поездов

Хранение расписания поездов, интерфейс ввода и редактирования, поиск
поездов на произвольную дату средствами MySQL. PHP 8.3 + MySQL 8.4, без фреймворка.

#### Запуск

```bash
cp env.example .env                                  # и поменять пароли
docker compose up -d --build
docker compose exec app composer dump-autoload -o
docker compose exec app php database/migrate.php
docker compose exec app php database/seed.php
```

Приложение поднимется на http://localhost:8080, порт меняется через `APP_PORT` в `.env`.

Сидер разворачивает расписание (поезда 21, 22, 45, 67, 35, 39) на 2022–2026 годы.

#### Диаграмма бд
```mermaid
erDiagram
    ROUTES ||--o{ TRIPS : "направление рейса"
    TRAINS ||--o{ TRIPS : "какой поезд едет"
    TRIPS ||--o{ DEPARTURES : "в какие дни идёт"

    ROUTES {
        int id PK
        varchar from_station UK "уникальны в паре с to_station"
        varchar to_station UK
    }

    TRAINS {
        int id PK
        varchar name UK "номер поезда"
    }

    TRIPS {
        int id PK
        int train_id FK
        int route_id FK
        time departure_time "время по умолчанию"
    }

    DEPARTURES {
        int id PK
        int trip_id FK
        date date UK "уникальна в паре с trip_id"
        time departure_time "время именно этого дня"
    }
```

#### Поиск
| Что ищем | Пример |
| --- | --- |
| конкретный поезд на дату | `/?train=45&date=2022-04-01` |
| маршрут на дату | `/?from=Минск&to=Гомель&date=2022-05-09` |
| любой поезд на дату | `/?date=2022-05-09` |

Фильтры комбинируются, пустые поля игнорируются.

#### Как пользоваться

1. Бургер меню где можно выбрать необходимое действие
<img width="2487" height="1161" alt="Image" src="https://github.com/user-attachments/assets/aeeaafae-9d63-4cf9-8bf4-0f0342f359f4" />
2. Страница поиска расписания
<img width="2487" height="1161" alt="Image" src="https://github.com/user-attachments/assets/73175861-a6d5-4c5b-be9e-480c35f7c67f" />
3. Все расписания (пагинация)
<img width="2487" height="1161" alt="Image" src="https://github.com/user-attachments/assets/4d546bb7-34b0-4f33-9477-a28d4c6ef639" />
4. Добавить расписание (вариант как делают в депо, проставка галочками)
<img width="2487" height="1161" alt="Image" src="https://github.com/user-attachments/assets/76a6fcbc-edc5-4d78-ba5b-e4a6d2b0a300" />
<img width="2487" height="1161" alt="Image" src="https://github.com/user-attachments/assets/80012de4-1fb4-4f1b-8827-7932786929db" />
5. Календарь рейса `/schedules/{id}` — правка по месяцам. Галочка добавляет или убирает день,
   поле времени рядом задаёт время именно этого дня. Сохраняется месяц целиком.
<img width="2487" height="1161" alt="Image" src="https://github.com/user-attachments/assets/c259fb9e-b486-4ba1-a473-5d98d5a1e668" />

#### Структура

```
app/Core          PDO, роутер, вьюхи, Request, Flash, CSRF
app/Http          контроллер и form-request'ы с валидацией входа
app/Services      ScheduleService — бизнес-логика
app/Repositories  SQL
app/Models        Trip, Train, Route — сущности
app/Dto           TripFilter — фильтр поиска
app/Support       Format, Calendar, ValidatesDates
database          миграции и сидер
resources/views   шаблоны
```
