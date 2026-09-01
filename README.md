# FusaShop - E-commerce Platform

Plataforma de comercio electrónico construida con Laravel y Vue.js

## 🚀 Despliegue

Esta aplicación requiere un servidor con:
- PHP 8.2+
- MySQL/PostgreSQL
- Node.js para el build del frontend

### Opciones de despliegue:

1. **Railway** (Recomendado)
2. **Render**
3. **Heroku**
4. **DigitalOcean App Platform**

## 📋 Características

- Sistema de autenticación completo
- Catálogo de productos
- Carrito de compras
- Sistema de pagos
- Chat en tiempo real
- Panel de administración
- Análisis con Python

## 🛠️ Instalación local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

