# Plan de Pruebas de Software

> **Estado del documento:** plan de pruebas **histórico/parcial** (v1.0, 02/04/2026). Describe alcance **planificado TO-BE**; no refleja por completo el estado V1 del repositorio.
>
> **Referencia vigente V1:** [`informe-pruebas.md`](informe-pruebas.md) · **Trazabilidad formal:** [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) · **DRS vigente:** [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md)

## Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil — SIDERAE-Blenkir

**Área:** Informática  
**Responsable:** Mg. Maglioni Arana Caparachin  
**Fecha:** 02/04/2026  
**Versión:** 1.0

---

## Tabla de contenido

1. [Historial de versiones](#1-historial-de-versiones)
2. [Información del proyecto](#2-información-del-proyecto)
3. [Aprobaciones](#3-aprobaciones)
4. [Resumen ejecutivo](#4-resumen-ejecutivo)
5. [Alcance de las pruebas](#5-alcance-de-las-pruebas)
6. [Criterios de aceptación o rechazo](#6-criterios-de-aceptación-o-rechazo)
7. [Entregables](#7-entregables)
8. [Recursos](#8-recursos)
9. [Planificación y organización](#9-planificación-y-organización)
10. [Premisas](#10-premisas)
11. [Dependencias y riesgos](#11-dependencias-y-riesgos)
12. [Referencias](#12-referencias)
13. [Glosario](#13-glosario)
14. [Estrategia de ejecución de pruebas por sprints](#14-estrategia-de-ejecución-de-pruebas-por-sprints)

---

## 1. Historial de versiones

| Fecha      | Versión | Autor                | Organización                      | Descripción                                                                        |
| ---------- | ------- | -------------------- | --------------------------------- | ---------------------------------------------------------------------------------- |
| 02/04/2026 | 1.0     | Carhuamaca / Chuchon | SIDERAE-Blenkir / Colegio Blenkir | Frontend en React 18 y Backend en Laravel ^13. Arquitectura desacoplada con Docker. |

---

## 2. Información del proyecto

| Campo                           | Valor                                                                                                   |
| ------------------------------- | ------------------------------------------------------------------------------------------------------- |
| **Empresa / Organización**      | Colegio Blenkir — IEP Blenkir Chilca / IEP Blenkir Auquimarca, Huancayo, Junín                          |
| **Proyecto**                    | Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil (SIDERAE-Blenkir) |
| **Fecha de preparación**        | 02/04/2026                                                                                              |
| **Cliente**                     | Colegio Blenkir — Dir. Willmahan Osores Paucarchuco                                                     |
| **Patrocinador principal**      | Dirección Institucional — Colegio Blenkir                                                               |
| **Gerente / Líder de proyecto** | Diego Ricardo Carhuamaca Vasquez                                                                        |
| **Gerente / Líder de pruebas**  | Ernesto Marcial Constantino Chuchon Sotelo                                                              |

---

## 3. Aprobaciones

| Nombre y Apellido                 | Cargo             | Departamento u organización | Fecha      | Firma |
| --------------------------------- | ----------------- | --------------------------- | ---------- | ----- |
| Diego Carhuamaca Vasquez          | Líder de proyecto | SIDERAE-Blenkir             | 02/04/2026 |       |
| Ernesto Chuchon Sotelo            | Líder de pruebas  | SIDERAE-Blenkir             | 02/04/2026 |       |
| Dr. Maglioni Arana Caparachin     | Docente / Asesor  | Sección NRC 28597           | 02/04/2026 |       |
| Dir. Willmahan Osores Paucarchuco | Director          | Colegio Blenkir             | 02/04/2026 |       |

---

## 4. Resumen ejecutivo

El presente documento constituye el Plan de Pruebas de Software del sistema SIDERAE-Blenkir, desarrollado para el Colegio Blenkir de Huancayo, Junín. Cubre la totalidad de los veinte (20) requerimientos funcionales definidos en el Formato 06, trazables con el proceso TO-BE y los problemas identificados en el análisis AS-IS del Formato 04.

El sistema se implementa con arquitectura desacoplada utilizando un frontend en React 18, un backend con Laravel **^13.0** (PHP 8.3) y MySQL 8, contenerizados y orquestados mediante Docker y Docker Compose.

**ML en V1 (estado real):** microservicio Python/Flask **determinístico** consumido desde Laravel vía HTTP. Los modelos **Random Forest, SVM y XGBoost** del DRS formal **no están implementados** en el repositorio V1; constituyen brecha/futuro (véase DRS v2 §RF-06, RF-18).

**Restricciones:** equipo de 2 desarrolladores que asumen también el rol de testers. Las pruebas se ejecutan en entorno local basado en contenedores Docker (Ubuntu 22.04 LTS). Presupuesto limitado a herramientas de software libre.

---

## 5. Alcance de las pruebas

### 5.1 Elementos de pruebas

Módulos y componentes del sistema SIDERAE-Blenkir a probar (alcance **planificado**): Carga de Datos, Asistencia, Reportes Conductuales, Motor ML, Alertas Tempranas, Intervención, Derivación, Dashboard/Panel, Usuarios y Roles, Validación de Datos, Log de Auditoría, Reentrenamiento ML, Semáforo de Completitud y Reporte ISO 9001.

**Matiz V1:** varios ítems anteriores están **pendientes, parciales o no confirmados** en el prototipo actual — ver [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md).

### 5.2 Nuevas funcionalidades a probar

> Lista **TO-BE / planificada**. No implica que todas estén implementadas en V1.

- El docente registra digitalmente la asistencia semanal sin usar papel. *(RF-02 confirmado curricular en V1.)*
- El coordinador importa resultados del Fast Test desde Excel y el sistema los vincula automáticamente. *(RF-03 **pendiente**.)*
- El psicólogo registra reportes conductuales integrados al perfil de riesgo. *(RF-04 **V1 mínimo cerrado** — API + UI perfil; 8 passed Fase 2E.)*
- El sistema genera automáticamente alertas tempranas con nivel de riesgo y recomendación. *(RF-08 confirmado en V1.)*
- El docente ve el perfil completo del estudiante en riesgo y registra la intervención. *(Alertas RF-09 confirmadas; UI riesgo en perfil **pausada** — RF-06 parcial.)*
- El directivo revisa el mapa de riesgo de todas las sedes y registra la derivación. *(Multi-sede y RF-10 **pendientes**; V1 opera sede **Chilca**.)*
- El administrador gestiona usuarios con roles y permisos diferenciados. *(RF-15 confirmado en V1.)*
- El sistema valida automáticamente la integridad de los datos antes de procesarlos. *(Parcial; RF-19 semáforo **implementado V1** — backend + UI + build OK; smoke manual pendiente.)*
- El directivo recibe un reporte mensual automático de calidad basado en ISO 25010. *(Referencia académica; **sin certificación ISO**.)*
- El administrador reentrena el modelo ML y visualiza las nuevas métricas. *(RF-18 **pendiente**; ML determinístico V1.)*

### 5.3 Pruebas de regresión

Se ejecutan tras cada corrección de defecto. Componentes afectados indirectamente:

- Motor ML (RF-06) cuando cambian módulos de carga de datos.
- Laravel Notifications/Alertas (RF-08) ante cambios en umbrales o respuesta ML.
- Dashboard en React (RF-14) ante modificaciones en la API y modelos.
- `spatie/activitylog` (RF-17) ante cualquier cambio en acciones de usuario.
- Semáforo de completitud (RF-19) ante cambios en la ingesta de datos. *(Implementado V1; `SemaforoCompletitudTest` 11 passed; UI React build OK; smoke manual pendiente.)*

### 5.4 Funcionalidades a no probar

- Integración directa con SIAGIE (API) — SIAGIE no dispone de API pública; importación SIAGIE global del DRS **pendiente en V1** (distinto de plantilla Excel curricular confirmada).
- Pruebas de carga con 1,082 usuarios simultáneos — el entorno local no replica la producción.
- Compatibilidad con navegadores distintos a Chrome y Firefox.
- Validación del modelo ML con datos reales del Blenkir — datos históricos no digitalizados.
- Pruebas de rendimiento del microservicio Flask bajo carga.

### 5.5 Enfoque de pruebas (estrategia)

Se aplica el **Modelo en V** con ejecución incremental por módulos, con relación directa al enfoque TDD (Test Driven Development). Las pruebas se integran en cada etapa del desarrollo y se ejecutan en contenedores Docker para garantizar reproducibilidad del entorno.

**Niveles de prueba:**

- **Unitarias** — PHPUnit para el backend y Jest + React Testing Library para el frontend React; caja blanca RF-06, 07, 16, 19.
- **Integración** — PHPUnit Feature Tests usando `Http::fake()` para simular el microservicio Flask ML (RF-01 al RF-09).
- **Sistema** — Cypress E2E planificado (**no encontrado en repo V1**), caja negra RF-01 al RF-20.
- **Funcionales** — Cypress planificado + casos manuales (informe V1: pruebas manuales recomendadas, sin Cypress).
- **Aceptación** — con el Colegio Blenkir, Cypress + escenarios reales (RF-02, 08, 09, 14).

**Tipos de pruebas ejecutadas:** funcionalidad, validación de datos, interfaz de usuario, seguridad y autenticación por roles, y auditoría conforme a ISO 25010.

---

## 6. Criterios de aceptación o rechazo

### 6.1 Criterios de aceptación

- El 100 % de los 20 RFs tiene al menos un caso de prueba ejecutado.
- El 95 % o más de los casos ejecutados tienen resultado Exitoso.
- Cero (0) defectos de severidad Crítica o Alta sin corregir al cierre.
- El 100 % de los defectos de severidad Media están corregidos o con plan de mitigación documentado.
- El 100 % de las pruebas de regresión (`php artisan test`) son exitosas tras cada corrección.
- Cada alerta tiene trazabilidad completa: generación → intervención → cierre (ISO 25010 / ISO 9001).
- El 100 % de los casos de control de acceso con Spatie Permission (RF-15) son exitosos.
- `spatie/activitylog` registra correctamente el 100 % de las acciones probadas (RF-17).

### 6.2 Criterios de suspensión

- Un defecto en el microservicio ML o en el módulo de autenticación (RF-15) impide ejecutar el resto de pruebas.
- Más del 40 % de los casos de la suite PHPUnit fallan en una ejecución, indicando falla sistémica.
- Los contenedores Docker (Frontend/Backend/BD) no logran levantarse o sincronizarse correctamente mediante `docker-compose`.
- Los datasets sintéticos o los seeders de Laravel presentan errores que invalidan los resultados.

### 6.3 Criterios de reanudación

- El defecto bloqueante ha sido corregido, verificado y aprobado por el líder de pruebas.
- Los contenedores están operativos, estables y la base de datos de pruebas está correctamente vinculada al contenedor de backend.
- La tasa de fallo baja por debajo del 20 % en una re-ejecución rápida con `php artisan test`.
- Los seeders y factories de Laravel han sido corregidos o reemplazados con datos válidos.
- El líder de pruebas aprueba formalmente la reanudación con un nuevo plan de sesión documentado.

---

## 7. Entregables

- Documento Plan de Pruebas de Software (presente documento).
- Casos de prueba por RF — fichas detalladas para los 20 RFs.
- Resultados PHPUnit / Jest / Cypress — salida de pruebas automatizadas con tasa de éxito por RF.
- Reporte de defectos / incidencias — listado con severidad, estado y responsable.
- Evidencias de pruebas — capturas de pantalla y reportes de Cypress.
- Reporte de cierre del plan — métricas de cobertura, tasa de éxito y defectos residuales (ISO 25010).

---

## 8. Recursos

### 8.1 Requerimientos de entornos — Hardware

| Equipo                    | Especificaciones mínimas                                    | Uso                                                           |
| ------------------------- | ----------------------------------------------------------- | ------------------------------------------------------------- |
| Computador Tester 1       | Intel Core i5 / RAM 16 GB / SSD 1 TB / Red 100 Mbps         | Pruebas de interfaz React, Jest, Cypress, pruebas funcionales |
| Computador Tester 2       | AMD Ryzen 5 5600G / RAM 16 GB / SSD 1 TB / Red 100 Mbps     | Pruebas PHPUnit, microservicio Flask, integración API         |
| Servidor local de pruebas | CPU 4 núcleos / RAM 16 GB / Disco 500 GB / Ubuntu 22.04 LTS | Laravel + MySQL + microservicio Flask en contenedores Docker  |
| Router / Switch LAN       | 100 Mbps                                                    | Red local para pruebas de comunicación Laravel ↔ Flask        |

### 8.2 Requerimientos de entornos — Software

| Software                      | Versión           | Licencia            | Uso en pruebas                                        |
| ----------------------------- | ----------------- | ------------------- | ----------------------------------------------------- |
| PHP                           | 8.3+              | PHP License (libre) | Runtime del backend Laravel                           |
| Laravel                       | **^13.0**         | MIT                 | Framework principal backend + routing + ORM + Queue (**stack vigente V1**) |
| React                         | 18.x              | MIT                 | Framework principal para la interfaz de usuario (SPA) |
| Node.js                       | 20+               | Open Source         | Entorno de ejecución para el proyecto React           |
| Cypress                       | 13.x (planeado)   | MIT                 | E2E **planeado** — **no confirmado en repo V1** (sin carpeta `cypress/`) |
| MySQL                         | 8.x               | GPL (libre)         | Base de datos relacional del sistema                  |
| Docker / Docker Compose       | Última            | Apache 2.0 (libre)  | Contenerización y orquestación de servicios           |
| Laravel Breeze                | 2.x               | MIT                 | Scaffold de autenticación (login, registro, sesión)   |
| Spatie Permission             | 6.x               | MIT                 | Gestión de roles y permisos (RF-15)                   |
| spatie/activitylog            | 4.x               | MIT                 | Log de auditoría automático (RF-17)                   |
| Laravel Excel (Maatwebsite)   | 3.x               | MIT                 | Importación plantilla Excel **curricular** (RF-01 parcial); **no** SIAGIE global |
| Barryvdh DomPDF               | 3.x               | MIT                 | Generación de reportes PDF (RF-16 parcial — export dashboard)             |
| Python + Flask                | 3.11 / 3.x        | PSF / BSD (libre)   | Microservicio ML **determinístico V1** (RF-06 parcial)                    |
| scikit-learn (ensemble DRS)   | 1.3+ (planeado)   | BSD (libre)         | RF/SVM/XGBoost del DRS — **no implementado**; brecha RF-06/RF-18          |
| PHPUnit                       | 11.x              | BSD (libre)         | Pruebas unitarias y de feature (`php artisan test`)   |
| Jest + React Testing Library  | Última            | MIT                 | Pruebas unitarias para componentes React              |
| Google Chrome / Firefox       | Última            | Libre               | Navegadores para pruebas de interfaz React y Cypress  |

### 8.3 Herramientas de pruebas requeridas

| Herramienta                            | Propósito en SIDERAE-Blenkir                                                                                            |
| -------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| PHPUnit (`php artisan test`)           | Pruebas unitarias de Services, Models, Helpers y clases de negocio; pruebas de Feature para endpoints y Jobs de Laravel |
| Cypress 13.x                           | E2E planificado — **no encontrado en repo V1**; usar smoke manual por rol ([`manual-usuario.md`](../manual-usuario.md)) |
| Jest + React Testing Library           | Pruebas unitarias de componentes, funciones y lógica del frontend en React                                              |
| Docker + Docker Compose                | Contenerización y orquestación del entorno de pruebas (Frontend React, Backend Laravel, MySQL 8, Microservicio Flask)   |
| Laravel Factories + Seeders            | Generación de datos sintéticos representativos para poblar la BD de pruebas automáticamente                             |
| `Http::fake()` de Laravel              | Simulación (mock) de las respuestas del microservicio Flask ML durante pruebas de integración                           |
| TablePlus / DBeaver                    | Verificación directa de la persistencia en MySQL: estudiantes, alertas, intervenciones, `activity_log`                  |
| Hoja de registro (Excel/Google Sheets) | Documentación manual de casos de prueba exploratorios, resultados, defectos y evidencias                                |

### 8.4 Personal

| Nombre                   | Rol en pruebas                     | Responsabilidades                                                                                                        |
| ------------------------ | ---------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| Diego Carhuamaca Vasquez | Líder de pruebas / Tester Frontend | Casos de prueba de interfaz React, dashboard, alertas, control de acceso; automatización con Cypress E2E y Jest          |
| Ernesto Chuchon Sotelo   | Tester Backend / ML                | Casos de prueba PHPUnit, microservicio Flask, validaciones Laravel, auditoría `spatie/activitylog`, pruebas de regresión |

### 8.5 Entrenamiento

- PHPUnit y Feature Tests en Laravel (`php artisan test`) — ambos testers.
- Cypress 13.x para pruebas E2E y Jest + React Testing Library para pruebas unitarias de React — Tester Frontend.
- Docker y Docker Compose: configuración y orquestación del entorno de pruebas — ambos testers.
- `Http::fake()` y mocking de servicios externos en Laravel — Tester Backend.
- Interpretación de métricas del modelo ML (accuracy, precision, recall, F1) — ambos testers.
- Criterios de calidad ISO 25010 e ISO 9001 aplicados a pruebas de software — ambos testers.
- Uso de Spatie Permission y `spatie/activitylog` en entorno de pruebas — ambos testers.

---

## 9. Planificación y organización

### 9.1 Procedimientos para las pruebas

| Paso | Actividad                                                                                                       | Responsable      |
| ---- | --------------------------------------------------------------------------------------------------------------- | ---------------- |
| 1    | Seleccionar el RF y revisar su ficha (precondiciones, flujo principal, alternativo)                             | Ambos testers    |
| 2    | Preparar entorno: ejecutar `docker-compose up -d`, luego `php artisan migrate:fresh --seed` en la BD de pruebas | Tester Backend   |
| 3    | Ejecutar el caso de prueba: PHPUnit para backend, o Jest/Cypress para el frontend React                         | Tester asignado  |
| 4    | Registrar resultado: Exitoso / Fallido / N/A con captura de evidencia o reporte generado por Cypress            | Tester asignado  |
| 5    | Si es Fallido, registrar defecto: ID, descripción, severidad, pasos para reproducir                             | Tester asignado  |
| 6    | Ejecutar el flujo alternativo del RF y registrar resultados                                                     | Tester asignado  |
| 7    | Reportar defectos al desarrollador responsable de la corrección                                                 | Líder de pruebas |
| 8    | Tras la corrección, re-ejecutar el caso fallido y la suite de regresión (`php artisan test`)                    | Ambos testers    |
| 9    | Actualizar el estado del caso y del defecto en el registro general                                              | Líder de pruebas |
| 10   | Al completar todos los RFs del módulo, generar el informe parcial del módulo                                    | Líder de pruebas |

### 9.2 Matriz de responsabilidades (RACI)

> R = Responsable · A = Aprobador · C = Consultado · I = Informado

| Actividad                                  | Diego (Líder) | Ernesto (Tester) | Dr. Maglioni (Asesor) | Dir. Blenkir (Cliente) |
| ------------------------------------------ | ------------- | ---------------- | --------------------- | ---------------------- |
| Diseño del plan de pruebas                 | R/A           | C                | C                     | I                      |
| Diseño de casos (Frontend React / Cypress) | R             | C                | I                     | I                      |
| Diseño de casos (Backend PHPUnit / ML)     | C             | R                | I                     | I                      |
| Ejecución de pruebas funcionales           | R             | R                | I                     | I                      |
| Ejecución PHPUnit (`php artisan test`)     | C             | R                | I                     | I                      |
| Ejecución Cypress E2E y Jest               | R             | C                | I                     | I                      |
| Registro y gestión de defectos             | A             | R                | I                     | I                      |
| Pruebas de regresión                       | C             | R                | I                     | I                      |
| Pruebas de aceptación con cliente          | R             | C                | C                     | A                      |
| Reporte de cierre del plan                 | R/A           | C                | A                     | I                      |
| Aprobación del plan                        | C             | C                | A                     | I                      |

### 9.3 Cronograma

| Fase      | Actividad                                                                                               | Duración         | Responsable          |
| --------- | ------------------------------------------------------------------------------------------------------- | ---------------- | -------------------- |
| 1         | Diseño del plan de pruebas y casos de prueba (PHPUnit + Jest + Cypress)                                 | 1 semana         | Diego / Ernesto      |
| 2         | Preparación del entorno: Docker Compose, `migrate:fresh --seed`, factories, configuración Cypress       | 3 días           | Ernesto              |
| 3         | Ejecución de pruebas unitarias PHPUnit (Services, Models, ML `Http::fake()`) y Jest (componentes React) | 1 semana         | Ernesto              |
| 4         | Ejecución de pruebas de integración (Feature Tests: carga → ML → alertas)                               | 1 semana         | Ambos                |
| 5         | Ejecución de pruebas funcionales del sistema completo (Cypress E2E)                                     | 2 semanas        | Ambos                |
| 6         | Corrección de defectos y pruebas de regresión (`php artisan test`)                                      | 1 semana         | Ambos                |
| 7         | Pruebas de aceptación y verificación post-implementación con el Colegio Blenkir                         | 3 días           | Diego / Dir. Blenkir |
| 8         | Documentación del reporte de cierre del plan                                                            | 2 días           | Diego                |
| **TOTAL** |                                                                                                         | **~6.5 semanas** |                      |

---

## 10. Premisas

- Los 20 RFs del Formato 06 están aprobados y no sufrirán cambios durante el periodo de pruebas.
- Los contenedores Docker (Frontend React, Backend Laravel, MySQL, Flask) estarán disponibles y operativos durante toda la ejecución del plan.
- Los Laravel Factories y Seeders serán suficientemente representativos de los datos reales del Colegio Blenkir.
- El equipo corregirá los defectos críticos en un máximo de 48 horas tras su reporte.
- Se usará metodología Modelo en V con ejecución incremental por módulos y enfoque TDD.
- PHPUnit, Cypress, Jest, Spatie Permission y demás paquetes estarán instalados antes de la Fase 3.
- El microservicio Flask ML estará levantado en su contenedor y accesible en el entorno de pruebas para las pruebas de integración reales.

---

## 11. Dependencias y riesgos

| Riesgo                                                                                  | Prob. | Impacto | Plan de mitigación                                                                                                           |
| --------------------------------------------------------------------------------------- | ----- | ------- | ---------------------------------------------------------------------------------------------------------------------------- |
| Datos históricos reales del Blenkir no disponibles para entrenar el modelo ML           | Alta  | Alto    | Usar Laravel Factories con datasets sintéticos calibrados; reentrenar con datos reales al disponerse                         |
| Defectos en el microservicio Flask que afecten múltiples RFs dependientes               | Media | Alto    | Usar `Http::fake()` de Laravel para aislar el microservicio; priorizar pruebas unitarias en Fase 3                           |
| Tiempo insuficiente para corregir defectos entre fases                                  | Media | Medio   | Ejecutar pruebas en orden de prioridad Alta → Media; diferir defectos Media con plan de mitigación                           |
| Fallo en la orquestación Docker Compose (contenedores no sincronizan)                   | Media | Alto    | Validar `docker-compose up -d` antes de cada sesión; documentar el estado esperado en el README del proyecto                 |
| El entorno local no replica correctamente producción                                    | Baja  | Medio   | Documentar diferencias; ejecutar smoke test en producción antes del despliegue final                                         |
| Cambios de requerimientos durante la ejecución del plan                                 | Baja  | Alto    | Congelar los RFs durante las pruebas; cualquier cambio requiere aprobación formal y nueva versión del plan                   |
| Baja disponibilidad del Colegio Blenkir para pruebas de aceptación                      | Media | Medio   | Agendar con al menos 2 semanas de anticipación; preparar escenarios Cypress observables en contenedores levantados           |
| Incompatibilidad de versiones entre paquetes Laravel (Spatie, DomPDF) o imágenes Docker | Baja  | Medio   | Fijar versiones exactas en `composer.json` y en el `Dockerfile` antes de iniciar; no actualizar dependencias durante el plan |

---

## 12. Referencias

| Documento                                                        | Autor                          | Versión         |
| ---------------------------------------------------------------- | ------------------------------ | --------------- |
| Formato 02 - Análisis del Proceso AS-IS                          | Carhuamaca / Chuchon           | 1.0             |
| Formato 03 - Diagrama BPM AS-IS                                  | Carhuamaca / Chuchon           | 1.0             |
| Formato 04 - Identificación de Problemas del Proceso             | Carhuamaca / Chuchon           | 1.0             |
| Formato 05 - Modelo BPM Mejorado TO-BE                           | Carhuamaca / Chuchon           | 1.0             |
| Formato 06 - Requerimientos Funcionales SIDERAE-Blenkir (20 RFs) | Carhuamaca / Chuchon           | 1.0 (corregida) |
| Diagnóstico Institucional Colegio Blenkir 2025-2026              | Consultor externo / NotebookLM | 1.0             |
| ISO 9001:2015 - Sistemas de Gestión de Calidad                   | ISO                            | 2015            |
| ISO/IEC 25010 - Calidad del Producto Software                    | ISO/IEC                        | 2023            |
| ISO/IEC 29119 - Pruebas de Software                              | ISO/IEC                        | 2013+           |
| Documentación oficial Laravel 13                                 | Laravel LLC                    | 13.x            |
| Documentación oficial React 18                                   | Meta / React Team              | 18.x            |
| Documentación Spatie Laravel Permission                          | Spatie                         | 6.x             |
| Documentación Docker y Docker Compose                            | Docker Inc.                    | Última          |
| Plan de Pruebas de Software - Plantilla                          | Mg. Maglioni Arana Caparachin  | 1.0             |
| Ejemplo Plan de Pruebas SIMCO                                    | Sebastian Zambrano             | 1.0             |

---

## 13. Glosario

| Término               | Definición                                                                                                                                                        |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| AS-IS                 | Proceso actual del Colegio Blenkir antes del SIDERAE-Blenkir; completamente manual y reactivo.                                                                    |
| TO-BE                 | Proceso mejorado con el SIDERAE-Blenkir; preventivo, automatizado y basado en datos.                                                                              |
| RF                    | Requerimiento Funcional: funcionalidad específica que el sistema debe implementar.                                                                                |
| Laravel               | Framework PHP de código abierto (MIT) que sigue el patrón MVC; base del backend de SIDERAE-Blenkir.                                                               |
| React 18              | Librería JavaScript de código abierto (MIT) para construir interfaces de usuario mediante componentes reutilizables; base del frontend SPA de SIDERAE-Blenkir.    |
| Tailwind CSS          | Framework CSS utilitario (MIT) para diseño responsivo de la interfaz React.                                                                                       |
| Eloquent              | ORM de Laravel para interactuar con la base de datos MySQL mediante modelos PHP.                                                                                  |
| Spatie Permission     | Paquete Laravel para gestión de roles y permisos de usuario (Administrador, Docente, Coordinador, Psicólogo, Directivo).                                          |
| spatie/activitylog    | Paquete Laravel que registra automáticamente en `activity_log` todas las acciones realizadas en el sistema.                                                       |
| PHPUnit               | Framework de pruebas unitarias para PHP, integrado en Laravel mediante `php artisan test`.                                                                        |
| Cypress               | Herramienta E2E **planeada** en este plan; **no confirmada en repo V1** (sin carpeta `cypress/`). |
| Jest                  | Framework de pruebas unitarias JavaScript para componentes React y lógica del frontend.                                                                           |
| React Testing Library | Librería complementaria a Jest para pruebas de componentes React centradas en el comportamiento del usuario.                                                      |
| `Http::fake()`        | Método de Laravel para simular (mock) respuestas HTTP externas; usado para aislar el microservicio Flask durante pruebas.                                         |
| Docker                | Plataforma de contenerización que empaqueta la aplicación y sus dependencias en contenedores aislados y reproducibles.                                            |
| Docker Compose        | Herramienta de orquestación que define y ejecuta múltiples contenedores Docker (Frontend, Backend, BD, Flask) mediante un archivo YAML.                           |
| ML                    | Machine Learning: en **V1** Flask **determinístico**; ensemble Random Forest/SVM/XGBoost del DRS = **brecha/futuro** (RF-06, RF-18). |
| Índice de riesgo      | Valor calculado por el servicio ML que clasifica a cada estudiante en nivel Alto, Medio o Bajo. |
| SIAGIE                | Sistema MINEDU Perú; importación global **pendiente en V1** (distinto de plantilla Excel curricular confirmada). |
| ISO 9001              | Norma de referencia académica para trazabilidad; **sin certificación** ni SGC auditado en V1. |
| ISO 25010             | Norma ISO/IEC que define las características de calidad del producto software: funcionalidad, rendimiento, seguridad, usabilidad, mantenibilidad y escalabilidad. |
| ISO 29119             | Estándar internacional que guía la estrategia, documentación y ejecución de pruebas de software.                                                                  |
| TDD                   | Test Driven Development: enfoque de desarrollo donde las pruebas se definen antes de implementar el código, guiando el diseño del sistema.                        |
| RACI                  | Matriz de responsabilidades: Responsable, Aprobador, Consultado, Informado.                                                                                       |

## 14. Estrategia de ejecución de pruebas por sprints

Las pruebas del sistema SIDERAE-Blenkir se ejecutarán de forma progresiva y alineada a los sprints de desarrollo, con el fin de asegurar la validación incremental del sistema y mantener coherencia entre implementación y verificación.

La distribución de pruebas es la siguiente:

- **Sprint 1: Infraestructura**
    - Validación de Docker Compose
    - Verificación de levantamiento de servicios
    - Pruebas de endpoints de health (`/api/health`, `/` en ML)

- **Sprint 2: Autenticación y control de acceso**
    - Pruebas de login y sesión
    - Validación de endpoint `/api/me`
    - Control de acceso por roles (RF-15)

- **Sprint 3A: Gestión de estudiantes**
    - Pruebas CRUD de estudiantes
    - Validación de persistencia en base de datos

- **Sprint 3B: Captura de datos**
    - Pruebas de carga de datos académicos (RF-01)
    - Pruebas de asistencia (RF-02)
    - Validación de variables socioeconómicas (RF-05)

- **Sprint 4: Integración con Machine Learning**
    - Pruebas de integración Laravel → Flask (RF-06)
    - Validación de cálculo del índice de riesgo (RF-07)

- **Sprint 5: Alertas e intervención**
    - Generación de alertas automáticas (RF-08)
    - Registro de intervenciones docentes (RF-09)
    - Flujo de atención y cierre de alertas (RF-13)

- **Sprint 6A: Visualización**
    - Pruebas del dashboard básico (RF-14)
    - Validación de visualización de riesgos

- **Sprint 6B: Funcionalidades avanzadas**
    - Filtros por rol y contexto
    - Exportación de reportes (RF-16)
    - Ajustes de visualización por usuario

Esta estrategia permite ejecutar las pruebas de manera incremental, asegurando que cada módulo desarrollado sea validado antes de avanzar al siguiente, en concordancia con el modelo en V y las buenas prácticas de ingeniería de software.
