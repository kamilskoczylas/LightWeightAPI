# LightWeightAPI

A lightweight, dependency-free PHP API framework for building simple JSON/File response APIs quickly.

## Purpose

Service-based framework designed for rapid development of PHP APIs with clean architecture and separation of concerns.

## Philosophy

- **As small as possible** - minimal codebase
- **As simple as possible** - easy to understand and maintain
- **As fast as possible** - optimized for performance
- **No external dependencies** - pure PHP only
- **No frameworks. No libraries.** - just clean architecture

## Features

- CRUD (Create, Read, Update, Delete) operations for custom entities
- MySQL & MariaDB support via PDO integration
- Basic validation of requests and responses
- Simplified automated testing for CRUD operations

## Requirements

- PHP 8 or higher
- PDO extension enabled

## Installation

The project uses `LightWeightAPI-framework` as a Git submodule located in the `lib` directory.

After creating or cloning the project, the `lib` directory may not contain the framework source yet. To initialize and fetch the submodule, run:

```bash
git submodule update --init --recursive
```

If you are cloning the project **for the first time**, you can fetch the submodule automatically with:

```bash
git clone --recurse-submodules https://github.com/kamilskoczylas/LightWeightAPI-skeleton
```

If the project has already been cloned without `--recurse-submodules`, use:

```bash
git submodule update --init --recursive
```

Update of lib dependencies to the newest version of the framework

```bash
git submodule update --remote lib
```


## Architecture

The framework follows a 4-layer architecture pattern:

1. **Request** - Validates input data
2. **Service** - Implements business logic
3. **Repository** - Handles database access
4. **Response** - Validates output data

Optional
5. **Adapter** - Adapter classes helps to separate third party dependencies from your code
6. **Mapper** - Mapper classes translates Requests to DTO

## Convention Naming

### Requests
```
Get|Update|Delete|Create + ServiceName + Operation + Request
```

### Responses
```
Get|Update|Delete|Create + ServiceName + Operation + Response
```

## Getting Started

### Step-by-Step Guide

1. Create a DTO class in `app/dto` folder (map SQL table to DTO class)
2. Create a request class in `app/request` folder with validation rules
3. Create a response class in `app/response` folder with validation rules
4. Create a service class in `app/service` folder and implement business logic
5. Create a repository class in `app/repository` folder for database access

### Usage Examples

```
GET:  api.php?request=GetUserMainRequest&user_id=1
GET:  api.php?request=GetUserAllRequest
POST: api.php?request=CreateUserRequest
      payload: {"user_name":"John Doe","user_email":"john.doe@example.com"}
```

## License

Licensed under the MIT License (2019-2026)
