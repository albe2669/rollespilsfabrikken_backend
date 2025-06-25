# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Quick Setup
- `make sync` - Full setup: install dependencies, migrate database, generate IDE helpers
- `make init` - Generate app key only
- `composer dev` - Start development server with queue worker and log viewer

### Database
- `php artisan migrate` - Run migrations
- `docker compose up -d` - Start MySQL database and phpMyAdmin

### Code Quality
- `make fix` - Run PHP CS Fixer and Laravel Pint formatters
- `composer run pint-check` - Check code style without fixing
- `composer run php-cs-fixer-check` - Check PHP CS Fixer rules

### IDE Integration
- `make ide` - Generate IDE helper files for PhpStorm/IDE autocompletion

### Git Hooks
- `make hooks` - Set up pre-commit hooks for code formatting

### Testing
- `php artisan test` - Run PHPUnit tests

## Architecture Overview

This is a Laravel 12 API backend for the Rollespilsfabrikken forum system using:
- **Authentication**: Laravel Sanctum with custom permission/role system
- **Database**: MySQL with UUID-based models using `dyrynda/laravel-model-uuid`
- **Search**: Laravel Scout with TNTSearch driver
- **File Handling**: Custom file upload system for posts/comments
- **Caching**: Laravel cache with Redis support

### Core Domain Models
- **Forum/Post/Comment**: Hierarchical forum structure with file attachments
- **Calendar/Event/Resource**: Event scheduling with resource management
- **User/Role/Permission**: Complex permission system with object-level permissions
- **SecurityQuestion**: Custom security questions for password reset

### Permission System Architecture
The app implements a sophisticated role-based permission system:
- Permissions can be granted at object level (per forum/calendar) or globally
- Users have roles, roles have permissions
- Policies in `app/Policies/` handle authorization logic
- Permission checking happens via Laravel's built-in authorization

### API Structure
- All routes in `routes/api.php` are API endpoints
- Controllers organized by domain in `app/Http/Controllers/`
- Request validation in `app/Http/Requests/API/`
- Resource transformers in `app/Http/Resources/`
- Most endpoints require Sanctum authentication

### Key Patterns
- Models use UUID primary keys via `GeneratesUuid` trait
- Extensive use of Laravel policies for authorization
- Observers in `app/Observers/` handle model events
- File uploads handled through custom file system with size tracking

### Frontend Integration
- API serves a separate Vue.js frontend
- Uses `resources/views/welcome.blade.php` for frontend deployment
- Frontend build process handled separately

## Tech Stack
- **PHP**: 8.3+
- **Laravel**: 12.x
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Testing**: PHPUnit
- **Code Style**: PHP CS Fixer + Laravel Pint
- **Development**: Docker for database, Nix for dependencies