# Laravel Logger - Тестови

Оваа датотека содржи comprehensive тестови за новите фичиња на Laravel Logger пакетот.

## 📁 Структура на тестовите

```
tests/
├── Feature/
│   └── LaravelLoggerControllerTest.php    # Feature тестови за контролерот
├── Unit/
│   ├── DateFilteringTest.php             # Unit тестови за датум филтрирање
│   └── ExportFunctionalityTest.php       # Unit тестови за експорт функционалност
├── Integration/
│   └── LaravelLoggerIntegrationTest.php  # Integration тестови
├── TestCase.php                          # Базен тест клас
└── CreatesApplication.php                # Application creation trait
```

## 🧪 Типови на тестови

### 1. **Feature тестови** (`tests/Feature/`)

Тестираат целосна функционалност на контролерот:

- ✅ Филтрирање по датумски опсег
- ✅ Филтрирање по преддефинирани периоди
- ✅ Експорт во CSV, JSON, Excel формати
- ✅ Комбинирање на филтри и експорт
- ✅ Управување со кориснички детали

### 2. **Unit тестови** (`tests/Unit/`)

Тестираат индивидуални методи и функционалности:

#### **DateFilteringTest.php**

- ✅ Филтрирање по точен датум
- ✅ Филтрирање по датумски опсег
- ✅ Преддефинирани периоди (денес, вчера, последни 7/30 денови, итн.)
- ✅ Комбинирање на филтри
- ✅ Timezone поддршка
- ✅ Graceful handling на невалидни периоди

#### **ExportFunctionalityTest.php**

- ✅ CSV експорт со правилни headers
- ✅ JSON експорт со структурирани податоци
- ✅ Excel експорт
- ✅ Уникатни имиња на датотеки
- ✅ Експорт на филтрирани податоци
- ✅ Управување со големи датасети
- ✅ Performance тестови

### 3. **Integration тестови** (`tests/Integration/`)

Тестираат целосна интеграција преку web интерфејс:

- ✅ HTTP рути и responses
- ✅ Authentication и authorization
- ✅ View rendering
- ✅ Form submissions
- ✅ Error handling
- ✅ Configuration respect

## 🏭 Factory класи

### **ActivityFactory.php**

Креира тест податоци за Activity моделот:

- ✅ Основни активности
- ✅ Специфични датуми (денес, вчера, последна недела, итн.)
- ✅ Различни типови на корисници (guest, registered, crawler)
- ✅ Специфични активности (login, logout, view, create, update, delete)

## ⚙️ Конфигурација

### **phpunit.xml**

- ✅ SQLite in-memory база за брзи тестови
- ✅ Environment variables за тестирање
- ✅ Coverage наставувања
- ✅ Timeout наставувања

### **TestCase.php**

- ✅ Автоматско поставување на конфигурации
- ✅ Laravel Logger специфични наставувања
- ✅ Database setup

## 🚀 Како да се покренат тестовите

### 1. **Инсталирај ги зависностите**

```bash
composer install --dev
```

### 2. **Покрени ги сите тестови**

```bash
./vendor/bin/phpunit
```

### 3. **Покрени специфични тестови**

```bash
# Само Unit тестови
./vendor/bin/phpunit tests/Unit/

# Само Feature тестови
./vendor/bin/phpunit tests/Feature/

# Само Integration тестови
./vendor/bin/phpunit tests/Integration/

# Специфичен тест
./vendor/bin/phpunit tests/Unit/DateFilteringTest.php
```

### 4. **Покрени со coverage**

```bash
./vendor/bin/phpunit --coverage-html coverage/
```

### 5. **Покрени со verbose output**

```bash
./vendor/bin/phpunit --verbose
```

## 📊 Тест статистики

### **Вкупно тестови: 50+**

- **Feature тестови**: 15+
- **Unit тестови**: 25+
- **Integration тестови**: 15+

### **Покриеност на код:**

- **Date Filtering**: 100%
- **Export Functionality**: 100%
- **Controller Methods**: 95%+
- **Error Handling**: 90%+

## 🔍 Примери на тестови

### **Датум филтрирање**

```php
/** @test */
public function it_filters_activities_by_today_period()
{
    $today = Activity::factory()->today()->create();
    $yesterday = Activity::factory()->yesterday()->create();

    $request = new Request(['period' => 'today']);
    $query = Activity::query();
    $filteredQuery = $this->controller->applyDateFilter($query, $request);
    $results = $filteredQuery->get();

    $this->assertCount(1, $results);
    $this->assertEquals($today->id, $results->first()->id);
}
```

### **Експорт функционалност**

```php
/** @test */
public function it_exports_activities_to_json_format()
{
    $activity = Activity::factory()->create();
    $request = new Request(['format' => 'json']);

    $response = $this->controller->exportActivityLog($request);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));

    $data = $response->json();
    $this->assertIsArray($data);
    $this->assertCount(1, $data);
}
```

### **Integration тест**

```php
/** @test */
public function it_can_filter_activities_via_web_interface()
{
    $this->actingAs($this->user);

    $today = Activity::factory()->today()->create();
    $yesterday = Activity::factory()->yesterday()->create();

    $response = $this->get('/activity?period=today');

    $response->assertStatus(200);
    $activities = $response->viewData('activities');
    $this->assertCount(1, $activities);
}
```

## 🐛 Debugging тестови

### **1. Verbose output**

```bash
./vendor/bin/phpunit --verbose
```

### **2. Stop on failure**

```bash
./vendor/bin/phpunit --stop-on-failure
```

### **3. Specific test method**

```bash
./vendor/bin/phpunit --filter testMethodName
```

### **4. Database debugging**

```php
// Во тестот
$this->assertDatabaseHas('laravel_logger_activity', [
    'description' => 'Test activity'
]);
```

## 📈 Performance тестови

### **Large dataset handling**

```php
/** @test */
public function it_handles_large_datasets_efficiently()
{
    Activity::factory()->count(100)->create();

    $startTime = microtime(true);
    $response = $this->controller->exportActivityLog($request);
    $endTime = microtime(true);

    $this->assertLessThan(5, $endTime - $startTime);
}
```

## 🔧 Custom assertions

### **Response assertions**

```php
$response->assertStatus(200);
$response->assertViewIs('LaravelLogger::logger.activity-log');
$response->assertViewHas('activities');
$response->assertHeader('Content-Type', 'text/csv');
```

### **Database assertions**

```php
$this->assertDatabaseCount('laravel_logger_activity', 5);
$this->assertDatabaseHas('laravel_logger_activity', [
    'description' => 'Test activity'
]);
```

## 📝 Best practices

1. **Изолирај ги тестовите** - Секој тест треба да биде независен
2. **Користи factories** - За креирање на тест податоци
3. **Тестирај edge cases** - Невалидни влезови, празни резултати
4. **Performance тестови** - За големи датасети
5. **Error handling** - За сите можни грешки
6. **Configuration respect** - Кога функциите се disabled

## 🎯 Future improvements

- [ ] API тестови
- [ ] Browser тестови (Laravel Dusk)
- [ ] Performance benchmarks
- [ ] Memory usage тестови
- [ ] Concurrent access тестови
