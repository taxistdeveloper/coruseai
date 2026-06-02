# Графики преподавателей

## Способы заполнения графика (внутри сайта и др.)

| Способ | Docker | Где |
|--------|--------|-----|
| **Редактор на сайте** (SuperDoc) | Не нужен | Вкладка по умолчанию — настоящий .docx в браузере |
| **Word на компьютере** | Не нужен | Скачать → Word → загрузить |
| **ONLYOFFICE** | Нужен | Вкладка «ONLYOFFICE», если включён в конфиге |
| **Word Online** (Microsoft) | HTTPS + M365/WOPI для правки | Просмотр на боевом сервере; подробнее `docs/WORD_ONLINE.md` |

Настройка режимов: `app/config/editor.php`, `app/config/wordonline.php`

## Установка

```bash
mysql -u root -p < database/schema.sql
```

URL: **http://localhost/ecollege**

Демо: `admin` / `password`, `ivanov` / `password`

## Админ

1. Загрузить шаблон `grafik.docx`
2. Добавить преподавателя + нагрузку (модуль, часы, срок)

## ONLYOFFICE (опционально)

`app/config/onlyoffice.php` → `'enabled' => true` + Docker:

```bash
docker run -d -p 8080:80 --name onlyoffice onlyoffice/documentserver
```
