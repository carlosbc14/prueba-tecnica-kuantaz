# Prueba Técnica Kuantaz

API desarrollada en Laravel 12 como parte de una prueba técnica, priorizando buenas prácticas de arquitectura y eficiencia en el procesamiento de datos.

## Decisiones técnicas

-   **Separación de responsabilidades:**  
    Se implementaron servicios (`Services`) para consumir las APIs externas en lugar de realizar estas operaciones directamente en los controladores. Esto permite un código más limpio, desacoplado y fácil de testear o extender.

-   **Procesamiento eficiente de beneficios:**  
    Para asociar los beneficios con sus respectivos filtros y fichas, se obtienen ambos recursos y se transforman en arreglos asociativos donde los índices corresponden a los IDs (`id_programa` para filtros y `id` para fichas). De esta manera, al procesar cada beneficio, la búsqueda de su filtro o ficha asociada es inmediata (O(1)), optimizando el rendimiento y evitando búsquedas repetitivas en arreglos.

## Requisitos

-   PHP >= 8.2
-   Composer

## Instalación

1. Clonar repositorio:

    ```sh
    git clone https://github.com/carlosbc14/prueba-tecnica-kuantaz.git
    cd prueba-tecnica-kuantaz
    ```

2. Instalar dependencias de PHP:

    ```sh
    composer install
    ```

3. Copiar archivo de entorno:

    ```sh
    cp .env.example .env
    ```

4. Generar clave de aplicación:
    ```sh
    php artisan key:generate
    ```

## Ejecución

Iniciar servidor de desarrollo:

```sh
php artisan serve
```

La API estará disponible en `http://localhost:8000`.

## Endpoints principales

### Obtener beneficios procesados

-   **GET** `/api/beneficios-procesados`

    Devuelve los beneficios agrupados por año, con información de fichas asociadas.

    **Respuesta de ejemplo:**

    ```json
    {
        "code": 200,
        "success": true,
        "data": [
            {
                "anio": 2023,
                "cantidad_total": 2,
                "monto_total": 4500,
                "beneficios": [
                    {
                        "id_programa": 101,
                        "monto": 1500,
                        "fecha_recepcion": "01/01/2023",
                        "fecha": "2023-01-01",
                        "ficha": {
                            "id": 10,
                            "nombre": "Ficha A",
                            "id_programa": 101,
                            "url": "ficha_a",
                            "categoria": "Categoria A",
                            "descripcion": "Descripción A"
                        }
                    }
                ]
            }
        ]
    }
    ```

### Documentación OpenAPI

-   **GET** `/api/documentation`

    Devuelve la documentación de la API generada automáticamente con [Swagger](https://swagger.io/).

### Estado de la API

-   **GET** `/api`

    Devuelve información básica de la API (descripción y versión).

## Pruebas

Ejecutar tests con:

```sh
php artisan test
```

## Estructura principal

-   [`app/Http/Controllers/BeneficioController.php`](app/Http/Controllers/BeneficioController.php): Controlador principal de la API.
-   [`app/Services/BeneficioService.php`](app/Services/BeneficioService.php): Servicio para obtener los beneficios desde API externa.
-   [`app/Services/FiltroService.php`](app/Services/FiltroService.php): Servicio para obtener filtros de los beneficios desde API externa.
-   [`app/Services/FichaService.php`](app/Services/FichaService.php): Servicio para obtener ficha de los beneficios desde API externa.
-   [`routes/api.php`](routes/api.php): Definición de rutas de la API.
