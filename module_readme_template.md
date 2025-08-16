Perfecto — te dejo **la documentación completa, clara y lista para que cualquier desarrollador** (junior, semi o senior) sepa exactamente **qué poner, dónde, por qué** y **cómo** trabajar dentro de un módulo en arquitectura limpia por módulos.
No hay código: todo son **normas, ejemplos descriptivos, plantillas mentales y flujos**. Si quieres después lo transformo a MD/DOCX listo para subir a un repo.

---

# Documentación: Módulo por Módulo (Arquitectura Limpia) — Guía completa

> Objetivo: que cualquiera que abra un módulo llamado `X` sepa en menos de 5 minutos dónde añadir una migración, un job, un mail, un evento, una entidad, un caso de uso o una prueba, y por qué van ahí.

---

## 1 — Estructura de carpetas (template canonical)

Raíz del módulo: `src/Modules/<ModuleName>/`

Dentro de cada módulo:

* `Domain/`

  * `Entities/`
  * `ValueObjects/`
  * `Repositories/`
  * `Services/` (domain services)
  * `Events/`
  * `Exceptions/` (excepciones de dominio)
* `Application/`

  * `DTOs/`
  * `UseCases/` (o `Services/` — aquí se llama "UseCases")
  * `Commands/` (si aplican comandos de aplicación)
  * `Jobs/`
  * `EventHandlers/` (listeners)
  * `Mails/` (si los mailables son lógicos de la aplicación)
  * `Responses/` (salidas específicas si no usas Resources)
* `Infrastructure/`

  * `Http/`

    * `Controllers/`
    * `Requests/`
    * `Resources/` (API resources / transformers)
  * `Persistence/`

    * `Eloquent/` (models, mappers, repositories)
    * `Mappers/`
  * `Database/`

    * `Migrations/`
    * `Seeders/`
    * `Factories/`
  * `Mail/` (plantillas/views de correo, si no están global)
  * `Notifications/`
  * `Services/` (adaptadores a APIs externas, drivers)
  * `Console/` (Artisan commands del módulo)
  * `Providers/` (ServiceProvider para registrar bindings y boot)
* `Config/` (configuración del módulo)
* `Tests/` (unitarios e integration tests del módulo)
* `README.md` (mini-guía específica del módulo)
* `CHANGELOG.md` (opcional para el módulo)

---

## 2 — Principios y responsabilidades (muy claras)

### Domain — ¿qué va y qué no va?

* **Va**: reglas de negocio puras, invariantes, identidad de objetos, Value Objects, Events, interfaces (Repositories).
* **No va**: dependencias de Laravel (no usar facades, no usar Eloquent), no envío de correos, no jobs, no validaciones HTTP.

**Propósito**: mantener el modelo del negocio independiente del framework.

### Application — ¿qué va y qué no va?

* **Va**: casos de uso (lo que el sistema hace), orquestación, trabajo transaccional (puede abrir/gestionar transacción), conversión DTO → Entity, disparar eventos de dominio, encolar jobs.
* **No va**: acceso directo a Eloquent (usar interfaces/Repositories), lógica de validación de bajo nivel del dominio (esa es Domain).

**Propósito**: coordinar, aplicar políticas de aplicación y servir de boundary entre la infra y el dominio.

### Infrastructure — ¿qué va y qué no va?

* **Va**: todo lo dependiente del framework o terceros: controladores, requests, resources, implementaciones de repositorios (Eloquent), mappers, migrations, seeders, factories, mailables, notificaciones, adaptadores.
* **No va**: lógica de negocio.

**Propósito**: adaptar el mundo exterior al dominio y viceversa.

---

## 3 — Qué debe contener cada carpeta (detalle + ejemplo textual)

> Para cada carpeta indico: *Qué poner*, *Quién lo escribe*, *Cuándo usarlo*, *Ejemplo de contenido (descriptivo)*.

### Domain/Entities/

* **Qué poner**: clases que representan objetos del negocio con identidad e invariantes.
* **Quién**: desarrollador backend que modela la lógica del negocio.
* **Cuándo**: cuando un concepto necesita reglas constantes (User, Todo, Order).
* **Ejemplo**: "UserEntity" → contiene id, email (como ValueObject), nombre y métodos para cambiar email, validar edad, marcar cuenta como activa, etc. No contiene persistencia.

### Domain/ValueObjects/

* **Qué poner**: tipos pequeños con validación propia e inmutables (Email, PasswordHash, Age, Address).
* **Cuándo**: cuando un valor tiene reglas o formato que deben cumplirse en cualquier entrada.
* **Ejemplo**: Email valida formato y normaliza a minúsculas; PasswordHash almacena el hash únicamente.

### Domain/Repositories/

* **Qué poner**: interfaces (contratos) de persistencia.
* **Quién**: equipo de dominio decide métodos necesarios.
* **Ejemplo**: UserRepositoryInterface con métodos: save(User): string, findById(UserId): ?User, findByEmail(Email): ?User.

### Domain/Services/

* **Qué poner**: lógica de dominio que no encaja en una sola entidad (por ejemplo, reglas complejas, cálculos).
* **Ejemplo**: PriceCalculator, MatchingPolicy.

### Domain/Events/

* **Qué poner**: definición de eventos de dominio que indican algo importante (UserRegistered, TodoCompleted).
* **Nota**: estos eventos son POPO (plain objects) sin dependencias.

### Application/DTOs/

* **Qué poner**: objetos planos para transportar datos. Se recomienda usar Spatie Data para validaciones/transformaciones.
* **Ejemplo**: CreateUserDTO con first\_name, last\_name, email, password, age, city. Usar DTO en interacciones entre Controller → UseCase y UseCase → Controller.

### Application/UseCases/

* **Qué poner**: cada "caso de uso" o "servicio de aplicación". Es la clase que ejecuta una acción (CreateUser, UpdateUser, DeleteUser, ListTodos).
* **Responsabilidad**: recibir DTO, construir Entity (con ValueObjects), pedir al RepositoryInterface que persista, manejar transacciones (si aplica), publicar eventos.
* **Ejemplo**: CreateUser use case crea UserEntity, llama a UserRepositoryInterface->save, publica UserRegistered.

### Application/Jobs/

* **Qué poner**: jobs que ejecutan tareas largas o externas (envío de mails, procesamientos).
* **Nota**: suelen ser dependientes de infraestructura (queue, mail).
* **Ejemplo**: SendWelcomeEmailJob recibe userId o email y envía mail en background.

### Application/EventHandlers/

* **Qué poner**: listeners que reaccionan a Domain Events y encolan Jobs o invocan acciones (stateless).
* **Ejemplo**: cuando se publica UserRegistered, se dispara OnUserRegistered handler que encola SendWelcomeEmailJob.

### Infrastructure/Http/Controllers/

* **Qué poner**: controllers del framework. Deben ser finos: validar request, convertir a DTO, llamar a UseCase, devolver Resource.
* **Regla**: nada de lógica de negocio en controllers.

### Infrastructure/Http/Requests/

* **Qué poner**: validaciones HTTP (FormRequest). Validar formatos, reglas de entrada. Convertir a DTO o pasar validated() al DTO factory.

### Infrastructure/Http/Resources/

* **Qué poner**: transformadores de salida (JSON). Mapear Entities o DTOs a estructuras amigables para API.

### Infrastructure/Persistence/Eloquent/

* **Qué poner**: Models Eloquent del módulo, y la implementación de los Repositories (mappers de Model ↔ Entity).
* **Regla**: Mapper centralizado para convertir Model ↔ Entity; la entidad del dominio no debe conocer el Model.

### Infrastructure/Database/Migrations, Seeders, Factories

* **Qué poner**: migraciones del módulo, seeders, factories.
* **Nota sobre migraciones por módulo**: registrar la ruta de migraciones del módulo en un ServiceProvider del módulo para que `php artisan migrate` y `migrate:fresh --seed` las detecten; además, incluir los seeders del módulo en `DatabaseSeeder` o un seeder maestro que los invoque.

### Infrastructure/Mail, Notifications

* **Qué poner**: mailables y notificaciones concretas que usan vistas, templates y están ligadas al framework.

### Infrastructure/Console

* **Qué poner**: comandos artisan específicos del módulo.

### Infrastructure/Services

* **Qué poner**: adaptadores a servicios externos (API clients, drivers). Implementan interfaces definidas quizás en Application o Domain.

### Infrastructure/Providers

* **Qué poner**: ServiceProvider del módulo. Registrar bindings de repositories (bind interfaz → implementación), registrar listeners de events, registrar rutas/migrations si aplica.

### Config/

* **Qué poner**: configuraciones del módulo (TTL, flags, webhooks), con `config('module.todo.xxx')`.

### Tests/

* **Qué poner**: tests unitarios para Domain y Application; tests de integración para Infrastructure. Organizar espejo de carpetas: Domain/..., Application/..., Infrastructure/...

### README.md y CHANGELOG.md (módulo)

* **Qué poner**: breve descripción, endpoints relevantes, tareas de setup, comandos especiales del módulo, convenciones y notes para desarrolladores.

---

## 4 — Flujos típicos (paso a paso, en lenguaje simple)

### A) Crear recurso (Create)

1. HTTP request llega al Controller.
2. Controller valida con FormRequest.
3. Controller crea DTO usando datos validados.
4. Controller llama al UseCase `CreateX.execute(dto)`.
5. UseCase transforma DTO → ValueObjects → Entity.
6. UseCase (opcional) abre transacción (si varias persistencias requieren atomicidad).
7. UseCase llama a `RepositoryInterface.save(Entity)`.
8. Repository impl (Eloquent) convierte Entity → Model y persiste.
9. UseCase publica Domain Event (p. ej. XCreated).
10. EventHandlers (Application) reaccionan: encolan Jobs, envían notificaciones.
11. Controller responde con Resource/DTO.

### B) Actualizar recurso (Update)

Semejante al Create, excepto que UseCase primero recupera por repo, aplica métodos de la Entity (ej. entity.rename()), valida, guarda.

### C) Operaciones asincrónicas (Jobs)

* Jobs se encolan desde EventHandlers o UseCases.
* Jobs son pequeños y dependientes de infra (mail, storage, APIs).
* Jobs deben recibir datos inmutables (ID, DTOs simples) o reconstruir entidad desde repo.

---

## 5 — Eventos vs Services (resumen ultra-simple)

* **Service / UseCase**: ejecuta la acción principal (registrar usuario).

  * Responsable de transact: sí (coordina).
  * Llama a repos, crea entity, publica evento.

* **Event**: aviso de que algo pasó (UserRegistered).

  * No ejecuta lógica pesada.
  * Los handlers reaccionan: enviar mail, dar puntos, etc.

* **Listener / Job**: ejecutan el trabajo consecuente (p. ej., envío de email). Jobs permiten reintentos y desacoplamiento.

---

## 6 — Reglas de diseño y buenas prácticas (con ejemplos de aplicación)

* **No llamar Eloquent desde Controller**: siempre pasar por UseCase → Repo.
* **Repositorios en Domain, implementaciones en Infra**.
* **Entities sin dependencias de framework**: no usar facades ni helpers de Laravel en Domain.
* **DTOs para entrada/salida**: Controller -> DTO -> UseCase.
* **ValueObjects para reglas inmutables**: Email, Uuid, Title.
* **Usar Mappers**: un único lugar para transformar Model ↔ Entity.
* **Registrar migraciones del módulo**: hacerlo desde el ServiceProvider del módulo.
* **Documentar cada módulo**: README con endpoints, env vars requeridas, dependencias externas.

---

## 7 — Convenciones de nombres (consistencia)

* Entidades: `PascalCase` `User`, `Todo`.
* ValueObjects: `PascalCase` con sufijo opcional `Email`, `UserId`.
* Repositories: interfaces `XRepositoryInterface`, impl `EloquentXRepository`.
* UseCases: `CreateUser`, `UpdateUser`.
* DTOs: `CreateUserDTO`, `UserData`.
* Events: `UserRegistered`.
* Jobs: `SendWelcomeEmailJob`.
* Mails: `WelcomeUserMail`.
* Resources: `UserResource`.
* Migrations: prefijo fecha + `create_<table>_table`.

---

## 8 — Calidad, testing y CI (qué incluir por módulo)

* **Tests**:

  * Domain: tests unitarios para Entities y ValueObjects (sin DB).
  * Application: tests unitarios para UseCases (mock de repos).
  * Infrastructure: tests de integración para Repositories, Controllers y Mappers (usa DB sqlite en memoria).
* **Linters**: Pint, PHPStan (Larastan), PHP Insights.
* **Pre-commit**: hooks para pint + phpstan + tests rápidos.
* **CI pipeline**: run static analysis → run tests (unit + integration) → php insights.

---

## 9 — Onboarding rápido (checklist para un dev que abre un módulo)

1. Leer `README.md` del módulo.
2. Revisar `Config/` por variables de entorno.
3. Mirar `Domain/Entities` para entender el modelo.
4. Ver `Application/UseCases` para conocer los puntos de entrada.
5. Revisar `Infrastructure/Http/Controllers` para endpoints públicos.
6. Si se necesita agregar una migración, crear en `Infrastructure/Database/Migrations` y registrar provider.
7. Añadir tests en `Tests/` antes de implementar la lógica.
8. Actualizar `CHANGELOG.md` si agregas una feature.

---

## 10 — Plantilla de README.md (mínimo requerido por módulo)

* Nombre del módulo (Descripción breve).
* Endpoints REST principales (ruta, método, body, response).
* Env vars requeridas.
* Comandos especiales (migrar solo este módulo, seeders).
* Instrucciones para ejecutar tests del módulo.
* Personas responsables / owner.
* Convenciones importantes (naming, limit complexity).

---

## 11 — Manejo de migraciones por módulo (concepto, no código)

* Mantén las migraciones dentro de `Infrastructure/Database/Migrations`.
* Para que `php artisan migrate` las ejecute, el módulo debe **registrar su carpeta** de migraciones durante el boot del ServiceProvider del módulo (esto es una práctica estándar).
* Además, agrega los seeders del módulo al seeder maestro o invócalos desde el DatabaseSeeder.

---

## 12 — Cómo documentar un cambio (PR template recomendado)

* Resumen de la feature o fix.
* Módulo(s) afectados.
* Migraciones añadidas (ruta).
* Nuevos env vars.
* Impacto en backward-compatibility.
* Checklist: tests añadidos, static analysis OK, docs README actualizada, changelog entry.

---

## 13 — Ejemplos concretos de "qué va donde" (frases cortas)

* Si has creado un nuevo objeto con reglas → Domain/Entities.
* Si has añadido validación de formulario → Infrastructure/Http/Requests.
* Si necesitas encolar envío de email → Application/Jobs + Infrastructure/Mail (mailable).
* Si creaste SQL o tabla nueva → Infrastructure/Database/Migrations.
* Si creaste DTOs para intercambio → Application/DTOs.
* Si implementaste repos con Eloquent → Infrastructure/Persistence/Eloquent.
* Si quieres publicar un evento cuando pasó algo → Domain/Events y UseCase lo dispara.

---

## 14 — Errores comunes y cómo evitarlos (anti-patterns)

* **Anti-pattern**: Guardar lógica de negocio en controllers.

  * *Solución*: mover a UseCase/Application Service.

* **Anti-pattern**: Usar facades en Entities.

  * *Solución*: Entities deben ser puros; inyectar adaptadores en Application.

* **Anti-pattern**: Repositorio en Infrastructure con firma pública diferente a la interfaz del Domain.

  * *Solución*: Implementar exactamente la interfaz Domain y usar mappers.

* **Anti-pattern**: Tests que dependen de estructura de DB real sin fixtures.

  * *Solución*: usar SQLite in-memory y factories por módulo.

---

## 15 — Visualización / Diagramas (ASCII — para pegar en docs)

Flujo básico de lectura/creación:

```
[Client HTTP]
    │
    ▼
Infrastructure/Http/Controllers/TodoController
    │ (validated request -> DTO)
    ▼
Application/UseCases/CreateTodo
    │ (builds ValueObjects -> Entity)
    │ (optional DB transaction)
    ▼
Domain/Repositories/TodoRepositoryInterface
    ▲
    │
Infrastructure/Persistence/EloquentTodoRepository -> Database
    │
    ▼
(optional) Domain/Event TodoCreated -> Application/EventHandlers -> Application/Jobs -> Infrastructure/Mail
```

Diagrama de responsabilidad:

```
Domain   <-- core rules, no infra deps
  ↑
  | UseCases (application) <-- orchestrate, use repos (interfaces)
  ↑
Infrastructure <-- controllers, Eloquent, mail, jobs, etc.
```

---

## 16 — Checklist de calidad antes de merge (por PR)

* [ ] ¿Entendiste el impacto en el dominio? (sí/no)
* [ ] Tests unitarios añadidos y pasan
* [ ] Tests de integración (si hay persistencia) añadidos y pasan
* [ ] Static analysis (PHPStan/Larastan) sin errores de gravedad
* [ ] Pint/Format aplicado
* [ ] Documentación del módulo actualizada (README)
* [ ] Cambelog actualizado
* [ ] Migraciones registradas y seeders añadidos si corresponde
* [ ] No hay dependencias de Laravel en Domain

---

## 17 — Glosario rápido (para nuevos devs)

* **Entity**: objeto de negocio con identidad y comportamiento.
* **Value Object**: valor pequeño, inmutable, con reglas propias.
* **Repository Interface**: contrato para persistir y recuperar Entities.
* **Use Case / Application Service**: clase que realiza una acción del negocio.
* **Event**: aviso de que algo pasó (no ejecuta lógica pesada).
* **Job**: tarea que puede correr en background (cola).
* **Mappers**: transforman Model ↔ Entity.
* **DTO**: datos planos para mover información entre capas.

---

## 18 — Recomendaciones finales (made by senior)

1. **Empieza simple**. No crees Domain Services ni ValueObjects si el caso es trivial. Añádelos cuando la lógica crezca.
2. **Prioriza testabilidad**: Entities y UseCases primero. Infra es lo que menos testeas unitariamente.
3. **Documenta en README** la intención del módulo: por qué existe y cuáles son sus límites.
4. **Revisiones de PR**: presta atención a dependencias de Domain (no deben existir).
5. **Mantén la consistencia** con esta guía: estructura, nombres y flujo serán tu mejor garantía de escalabilidad.
