# PrestaShop Abandoned Cart Recovery - Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        PrestaShop Store                          │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Module: pe_abandonedcart                      │
│                     Namespace: MLAB\PE\                          │
└─────────────────────────────────────────────────────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
┌───────────────┐      ┌───────────────┐      ┌───────────────┐
│     MODEL     │      │   CONTROLLER  │      │     VIEW      │
├───────────────┤      ├───────────────┤      ├───────────────┤
│ AbandonedCart │      │     Cron      │      │     Email     │
│   (extends    │◄────►│   Recovery    │◄────►│  Templates    │
│ ObjectModel)  │      │     Admin     │      │  Admin Views  │
└───────────────┘      └───────────────┘      └───────────────┘
        ▲                       ▲
        │                       │
        └───────────┬───────────┘
                    ▼
            ┌───────────────┐
            │    SERVICE    │
            ├───────────────┤
            │  Abandoned    │
            │ CartService   │
            │ (Business     │
            │   Logic)      │
            └───────────────┘
                    │
        ┌───────────┼───────────┐
        ▼           ▼           ▼
    ┌────────┐ ┌────────┐ ┌─────────┐
    │Database│ │  Mail  │ │PrestaShop│
    │  Layer │ │ System │ │   API   │
    └────────┘ └────────┘ └─────────┘
```

## Data Flow

### 1. Cart Tracking Flow
```
Customer adds to cart
        │
        ▼
PrestaShop Hook: actionCartSave
        │
        ▼
Module::hookActionCartSave()
        │
        ▼
AbandonedCartService::trackCart()
        │
        ▼
AbandonedCart Model::add/update()
        │
        ▼
Database: ps_abandoned_cart
```

### 2. Cron Job Flow
```
Cron trigger (every 15-30 min)
        │
        ▼
URL: /module/pe_abandonedcart/cron?token={TOKEN}
        │
        ▼
CronController::initContent()
        │
        ▼
Token validation
        │
        ▼
AbandonedCartService::processAbandonedCarts()
        │
        ├──► Query abandoned carts (delay > X minutes)
        │
        ├──► For each cart:
        │    │
        │    ├──► Generate product list HTML
        │    │
        │    ├──► Create recovery token
        │    │
        │    ├──► Send email (HTML + text)
        │    │
        │    └──► Mark email_sent = true
        │
        └──► Return JSON response
```

### 3. Cart Recovery Flow
```
Customer receives email
        │
        ▼
Customer clicks CTA button
        │
        ▼
URL: /module/pe_abandonedcart/recovery?token={TOKEN}
        │
        ▼
RecoveryController::initContent()
        │
        ▼
Token validation (64-char hex)
        │
        ▼
AbandonedCartService::recoverCart()
        │
        ├──► Find cart by token
        │
        ├──► Restore cart to session
        │
        ├──► Mark recovered = true
        │
        └──► Redirect to cart page
```

### 4. Admin Management Flow
```
Admin accesses back office
        │
        ▼
Orders > Abandoned Carts menu
        │
        ▼
AdminPeAbandonedCartController::renderList()
        │
        ├──► Display all abandoned carts
        │
        └──► Actions: View, Delete
                │
                ▼
        AdminPeAbandonedCartController::renderView()
                │
                ├──► Show cart details
                ├──► Show customer info
                ├──► Show product list
                ├──► Show recovery status
                └──► Show recovery URL
```

## Component Interactions

### Database Schema
```sql
ps_abandoned_cart
├── id_abandoned_cart (PK)
├── id_cart (FK → ps_cart)
├── id_customer (FK → ps_customer)
├── email
├── cart_data (JSON)
├── recovery_token (UNIQUE, INDEX)
├── date_add
├── date_upd
├── email_sent (BOOL)
├── date_email_sent
├── recovered (BOOL)
└── date_recovered
```

### Email Template Structure
```
Email (HTML + Text)
├── Header
│   └── Shop logo
├── Greeting
│   └── Personalized with customer name
├── Message
│   └── Cart abandoned notification
├── Products Section
│   ├── Product list (images, names, prices)
│   └── Cart total
├── CTA Button
│   └── Recovery link (unique token)
├── Additional Info
│   └── Help text
└── Footer
    └── Shop information
```

## Security Layers

```
┌─────────────────────────────────────────────────┐
│              Security Measures                   │
├─────────────────────────────────────────────────┤
│ 1. Token Authentication                         │
│    ├─ Cron: MD5 token                          │
│    └─ Recovery: 64-char hex token              │
├─────────────────────────────────────────────────┤
│ 2. Input Validation                             │
│    ├─ Token format validation (regex)          │
│    ├─ Email validation                          │
│    └─ Database input sanitization (pSQL)       │
├─────────────────────────────────────────────────┤
│ 3. SQL Injection Prevention                     │
│    ├─ Parameterized queries                    │
│    └─ PrestaShop ObjectModel                   │
├─────────────────────────────────────────────────┤
│ 4. XSS Prevention                               │
│    ├─ Output escaping (htmlspecialchars)       │
│    └─ Smarty auto-escaping                     │
├─────────────────────────────────────────────────┤
│ 5. Error Handling                               │
│    ├─ Try-catch blocks                         │
│    ├─ JSON error checking                      │
│    └─ Graceful failure                         │
├─────────────────────────────────────────────────┤
│ 6. Access Control                               │
│    ├─ Security index.php files                 │
│    ├─ Admin authentication                      │
│    └─ Customer session validation              │
└─────────────────────────────────────────────────┘
```

## Configuration Flow

```
Admin Configuration Page
        │
        ▼
Module::getContent()
        │
        ├──► Display configuration form
        │    │
        │    ├─ Enable/Disable toggle
        │    ├─ Email delay setting
        │    └─ Cron URL display
        │
        └──► Save configuration
             │
             └──► Configuration::updateValue()
                  │
                  ├─ PE_ABANDONED_CART_ENABLED
                  ├─ PE_ABANDONED_CART_DELAY
                  └─ PE_ABANDONED_CART_CRON_TOKEN
```

## Module Lifecycle

### Installation
```
1. Module::install()
2. ├─► installDb() - Create ps_abandoned_cart table
3. ├─► installTab() - Add admin menu
4. ├─► registerHook('actionCartSave')
5. └─► createCronTask() - Generate token & defaults
```

### Uninstallation
```
1. Module::uninstall()
2. ├─► uninstallDb() - Drop ps_abandoned_cart table
3. ├─► uninstallTab() - Remove admin menu
4. └─► Remove configuration values
```

## Integration Points

### PrestaShop Hooks
- **actionCartSave**: Triggered when cart is saved
  - Used for: Tracking cart changes

### PrestaShop APIs
- **Context**: Current shop/language/customer context
- **Configuration**: Module settings storage
- **Link**: URL generation (module links, image links)
- **Mail**: Email sending system
- **Tools**: Utility functions (getValue, redirect, displayPrice)
- **ObjectModel**: Database abstraction layer
- **Db**: Database query execution

### External Dependencies
- **Composer**: PSR-4 autoloading
- **PHP**: 7.2+ (json, random_bytes, etc.)
- **PrestaShop**: 1.7.0+ core API

## Performance Considerations

```
Optimization Strategy
├── Database Indexing
│   ├─ id_cart (for lookups)
│   ├─ id_customer (for joins)
│   └─ recovery_token (for recovery)
├── Query Efficiency
│   ├─ Single query for abandoned carts
│   └─ Batch processing in cron
├── Email Queue
│   └─ Process in batches (cron frequency)
└── Caching
    └─ Configuration values cached
```

## Error Handling Strategy

```
Error Types & Handling
├── Database Errors
│   └─ Return false, log error
├── JSON Errors
│   ├─ Check json_last_error()
│   └─ Provide fallback values
├── Email Errors
│   ├─ Mail::Send returns false
│   └─ Cart remains in pending state
├── Token Errors
│   ├─ Invalid format → 404
│   └─ Not found → Error page
└── Service Errors
    ├─ Try-catch in constructor
    └─ Null check before use
```

This architecture ensures a robust, secure, and maintainable abandoned cart recovery system for PrestaShop.
