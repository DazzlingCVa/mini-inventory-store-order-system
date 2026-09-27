# Store Order & Inventory Mini-System

A Laravel-based mini-system for managing products, customers, orders, inventory, low-stock products, and order confirmations.

## Features

- Create customer orders
- Product and customer management
- Order item management
- Subtotal, tax, and grand total calculation
- Stock availability validation
- Automatic stock deduction after successful order creation
- Transaction-based order creation
- Concurrent stock protection using `lockForUpdate()`
- Customer order history by email
- Configurable low-stock API
- Queued order confirmation
- Feature tests
- Factories and seeders
- Simple web UI

## Tech Stack

- PHP
- Laravel
- MySQL
- Blade
- JavaScript
- HTML/CSS
- Laravel Queue
- PHPUnit

## Database Structure

Main tables:

- `products`
- `customers`
- `orders`
- `order_items`

### Relationships

- Customer has many Orders
- Order belongs to Customer
- Order has many Order Items
- Order Item belongs to Product
- Product has many Order Items

## Installation

Clone the repository:

```bash
git clone YOUR_GITHUB_REPOSITORY_URL
cd YOUR_PROJECT_FOLDER