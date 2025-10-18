# Laravel Logger - Ново фичиња

## 📅 Филтрирање по датум

### Опис

Додадено е филтрирање по датумски опсег за подобро управување со активностите.

### Користење

#### 1. Филтрирање по датумски опсег

```php
// Во контролерот
$activities = Activity::whereDate('created_at', '>=', '2024-01-01')
                     ->whereDate('created_at', '<=', '2024-12-31')
                     ->get();
```

#### 2. Преддефинирани периоди

- `today` - Денес
- `yesterday` - Вчера
- `last_7_days` - Последни 7 денови
- `last_30_days` - Последни 30 денови
- `last_3_months` - Последни 3 месеци
- `last_6_months` - Последни 6 месеци
- `last_year` - Последна година

#### 3. URL параметри

```
/activity?date_from=2024-01-01&date_to=2024-12-31
/activity?period=last_7_days
```

### Конфигурација

```php
// config/laravel-logger.php
'enableDateFiltering' => env('LARAVEL_LOGGER_ENABLE_DATE_FILTERING', true),
```

## 📊 Експорт функционалност

### Опис

Додадена е функционалност за експорт на активности во различни формати.

### Поддржани формати

#### 1. CSV експорт

```php
// URL
/activity/export?format=csv

// Метод
public function exportToCsv($activities)
{
    $filename = 'activity_log_' . now()->format('Y-m-d_H-i-s') . '.csv';
    // ... имплементација
}
```

#### 2. JSON експорт

```php
// URL
/activity/export?format=json

// Метод
public function exportToJson($activities)
{
    $filename = 'activity_log_' . now()->format('Y-m-d_H-i-s') . '.json';
    // ... имплементација
}
```

#### 3. Excel експорт

```php
// URL
/activity/export?format=excel

// Метод
public function exportToExcel($activities)
{
    $filename = 'activity_log_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
    // ... имплементација
}
```

### Користење со филтри

```php
// Експорт со филтри
/activity/export?format=csv&date_from=2024-01-01&date_to=2024-12-31
/activity/export?format=json&period=last_7_days&user=123
```

### Конфигурација

```php
// config/laravel-logger.php
'enableExport' => env('LARAVEL_LOGGER_ENABLE_EXPORT', true),
```

## 🎨 Frontend интеграција

### Blade template пример

```blade
{{-- Додај го во activity-log.blade.php --}}
@include('LaravelLogger::partials.filter-export-form')
```

### JavaScript за динамично филтрирање

```javascript
// Додај го во scripts.blade.php
document.getElementById("period").addEventListener("change", function () {
  if (this.value) {
    document.getElementById("date_from").value = "";
    document.getElementById("date_to").value = "";
  }
});
```

## 🔧 API употреба

### Експорт преку API

```php
// Во контролерот
public function exportActivityLog(Request $request)
{
    $format = $request->get('format', 'csv');
    $activities = Activity::orderBy('created_at', 'desc');

    // Примени филтри
    if (config('LaravelLogger.enableDateFiltering')) {
        $activities = $this->applyDateFilter($activities, $request);
    }

    // Експорт
    switch ($format) {
        case 'csv':
            return $this->exportToCsv($activities->get());
        case 'json':
            return $this->exportToJson($activities->get());
        case 'excel':
            return $this->exportToExcel($activities->get());
    }
}
```

## 📝 Рути

### Нови рути

```php
// routes/web.php
Route::get('/export', ['uses' => 'LaravelLoggerController@exportActivityLog'])->name('export-activity');
```

## 🌐 Преводи

### Англиски (en)

```php
// resources/lang/en/laravel-logger.php
'filterAndExport' => 'Filter and Export',
'fromDate' => 'From Date',
'toDate' => 'To Date',
'exportCSV' => 'Export CSV',
'exportJSON' => 'Export JSON',
'exportExcel' => 'Export Excel',
// ... повеќе преводи
```

## 🚀 Инсталација и употреба

### 1. Инсталирај го пакетот

```bash
composer require jeremykenedy/laravel-logger
```

### 2. Публикувај ги конфигурациите

```bash
php artisan vendor:publish --provider="jeremykenedy\LaravelLogger\LaravelLoggerServiceProvider"
```

### 3. Додај ги рутите

```php
// routes/web.php
Route::group(['middleware' => ['web', 'auth']], function () {
    // Додај ги Laravel Logger рутите
});
```

### 4. Користи го во Blade

```blade
{{-- Во твојот layout --}}
@include('LaravelLogger::partials.filter-export-form')
```

## 🔍 Пример за користење

### Филтрирање и експорт

```php
// Во контролерот
public function getFilteredActivities(Request $request)
{
    $activities = Activity::orderBy('created_at', 'desc');

    // Примени филтри
    if ($request->filled('date_from')) {
        $activities->whereDate('created_at', '>=', $request->get('date_from'));
    }

    if ($request->filled('period')) {
        switch ($request->get('period')) {
            case 'last_7_days':
                $activities->where('created_at', '>=', now()->subDays(7));
                break;
            // ... повеќе случаи
        }
    }

    return $activities->get();
}
```

## 📊 Статистики

### Број на активности по период

```php
$todayCount = Activity::whereDate('created_at', today())->count();
$weekCount = Activity::where('created_at', '>=', now()->subDays(7))->count();
$monthCount = Activity::where('created_at', '>=', now()->subDays(30))->count();
```

## 🎯 Предности

1. **Подобрена перформанса** - Филтрирање на ниво на база на податоци
2. **Флексибилност** - Поддршка за различни формати на експорт
3. **Корисничко искуство** - Интуитивен интерфејс за филтрирање
4. **Скалабилност** - Ефикасно управување со големи количини на податоци
5. **Интеграција** - Лесна интеграција со постоечки системи
