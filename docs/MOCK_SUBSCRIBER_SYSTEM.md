# Mock Subscriber System Documentation

## Overview

The **Mock Subscriber System** is an interchangeable simulation layer for the ISP customer services platform. It allows complete development and testing of subscriber lookups, quota checks, recharge flows, package modifications, and external failure handling without relying on the physical external ADV/ISP Radius server.

---

## 1. Architecture

The system follows Clean Architecture principles with dependency inversion:

```
Laravel Application / Controllers (SubscriberApiController)
                          ↓
              SubscriberProviderInterface
                          ↓
        ┌─────────────────┴─────────────────┐
        ▼                                   ▼
MockSubscriberProvider            AdvSubscriberProvider (Future)
(Local mock DB + Simulations)     (Real External ADV REST API)
```

- **Zero Coupling**: Application controllers depend exclusively on `App\Contracts\SubscriberProviderInterface` and DTOs (`SubscriberData`, `SubscriptionData`, `UsageData`, `BorrowingData`, `RechargeResult`, `PackageChangeResult`).
- **Centralized Resolution**: The binding is registered in `App\Providers\SubscriberServiceProvider` and controlled via `.env`:
  ```env
  SUBSCRIBER_PROVIDER=mock
  ```

---

## 2. Verification & Testing

To run the complete automated test suite verifying both the unit provider logic and HTTP API endpoints:

```powershell
# Run full PHPUnit test suite (PHP 8.3+)
& "C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" vendor/phpunit/phpunit/phpunit

# Run unit tests for MockSubscriberProvider
& "C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" vendor/phpunit/phpunit/phpunit tests/Unit/MockSubscriberProviderTest.php

# Run feature tests for SubscriberApiController
& "C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" vendor/phpunit/phpunit/phpunit tests/Feature/SubscriberApiTest.php
```

---

## 3. Database Seeding & Reset

To reset the development database and seed all test packages and mock subscribers:

```powershell
& "C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" artisan migrate:fresh --seed
```

### Production Safety Guard
Both `MockPackageSeeder` and `MockSubscriberSeeder` have strict environment guards:
```php
if (app()->environment('production')) {
    return;
}
```
No dummy records will ever be seeded into production.

---

## 4. Test Subscribers Catalog & Edge Cases

All development subscribers use the predictable format `TEST-1000XX`:

| Contract Number | Subscriber Name | Status | Package | Allowance | Used Quota | Borrowing State | Test Scenario Description |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `TEST-100001` | خالد محمد عبد الله | `ACTIVE` | ميقا 100G | 100 GB | 35.50 GB | `NOT_ELIGIBLE` | **Normal Baseline**: Healthy active subscriber with standard usage. |
| `TEST-100002` | سالم مفتاح علي | `ACTIVE` | ميقا 150G | 150 GB | 142.30 GB | `AVAILABLE` | **High Usage / Low Quota**: Remaining quota < 10%. Eligible for borrowing. |
| `TEST-100003` | الهادي أحمد ربيض | `ACTIVE` | ميقا 70G | 79 GB | 78.50 GB | `AVAILABLE` | **Exhausted Quota**: 99% used (0.5 GB remaining). |
| `TEST-100004` | عمر عبد اللطيف | `EXPIRED` | ميقا 70G | 79 GB | 79.00 GB | `AVAILABLE` | **Expired Subscription**: Expired 5 days ago, 100% used. |
| `TEST-100005` | مروان السنوسي | `SUSPENDED` | ميقا 150G | 150 GB | 150.00 GB | `NOT_ELIGIBLE` | **Suspended Account**: Recharges and package changes return HTTP 403 Forbidden. |
| `TEST-100006` | طارق الزوي | `EXPIRED` | ميقا 70G | 79 GB | 79.00 GB | `ACTIVE` | **Active Borrowing**: Subscription expired, emergency grace period active (22.5 GB used). |
| `TEST-100007` | منيرة الفيتوري | `ACTIVE` | ميقا 100G | 100 GB | 96.00 GB | `AVAILABLE` | **Borrowing Available**: Quota nearly depleted, borrowing available to claim. |
| `TEST-100008` | فاطمة عبد السلام | `EXPIRED` | ميقا 150G | 150 GB | 150.00 GB | `EXPIRED` | **Borrowing Expired**: Both subscription and emergency grace period are expired. |
| `TEST-100009` | مصطفى قاسم | `ACTIVE` | ميقا 300G | 300 GB | 0.00 GB | `NOT_ELIGIBLE` | **Fresh Recharge**: 0% used, fresh 30-day validity. |
| `TEST-100010` | إبراهيم المبروك | `ACTIVE` | ميقا 500G | 500 GB | 125.00 GB | `NOT_ELIGIBLE` | **High-Tier / Fast**: 500G tier testing. |
| `TEST-100011` | عائشة التاورغي | `ACTIVE` | ميقا 100G | 100 GB | 55.40 GB | `NOT_ELIGIBLE` | **Missing Optional Phone**: Tests null phone field handling. |
| `TEST-100012`..`25` | Various Libyan names | Diverse | PKG-70 - 500 | 79 - 500 GB | Varied | Varied | **Demographic Distribution**: For testing searches, filters, pagination, and stats. |

---

## 5. Failure Simulation Triggers

The mock provider simulates external ISP API network and server failures without external tools:

| Contract / Trigger | Simulated Condition | Thrown Exception | HTTP API Response |
| :--- | :--- | :--- | :--- |
| `SIM-TIMEOUT` or `*TIMEOUT*` | Provider Gateway Timeout | `SubscriberProviderUnavailableException::timeout()` | `HTTP 504 Gateway Timeout` |
| `SIM-500` or `*SERVER-ERROR*` | Provider Server 500 Crash | `SubscriberProviderUnavailableException::serverError()` | `HTTP 503 Service Unavailable` |
| Non-existent contract | Subscriber Not Found | `SubscriberNotFoundException` | `HTTP 404 Not Found` |
| Suspended subscriber contract | Subscriber Account Suspended | `SubscriberSuspendedException` | `HTTP 403 Forbidden` |
| Duplicate / Same Package | Same Package Selected | `PackageChangeFailedException` | `HTTP 422 Unprocessable Content` |
| Invalid Package ID | Non-existent Package ID | `RechargeFailedException` | `HTTP 422 Unprocessable Content` |

---

## 6. Idempotency Support

`POST /api/v1/subscribers/{contractNumber}/recharge` and `POST /api/v1/subscribers/{contractNumber}/change-package` support the `X-Idempotency-Key` HTTP header.
Repeated requests with identical idempotency keys return the cached result immediately, guaranteeing that network retries do not trigger duplicate recharges or repeated billing operations.

---

## 7. Migration to Real ADV Provider

When the real ADV API becomes available:
1. Implement `App\Integrations\Subscriber\Adv\AdvSubscriberProvider` implementing `SubscriberProviderInterface`.
2. Register the binding in `App\Providers\SubscriberServiceProvider`:
   ```php
   return match ($provider) {
       'adv' => $app->make(AdvSubscriberProvider::class),
       default => $app->make(MockSubscriberProvider::class),
   };
   ```
3. Update `.env`:
   ```env
   SUBSCRIBER_PROVIDER=adv
   ADV_API_URL=https://api.adv-radius.local/v1
   ADV_API_KEY=your-production-key
   ADV_TIMEOUT=10
   ```
4. **No other controllers, services, DTOs, or application business logic require any modification.**
