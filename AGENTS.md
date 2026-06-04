# Guía para agentes — SIDERAE-Blenkir

Contexto operativo del repositorio. Las reglas completas de desarrollo están en [`.cursorrules`](.cursorrules).

## Decisión operativa: sede única Chilca

Criterio vigente para **V1** del prototipo:

| Tema | Regla |
|------|--------|
| Operación | Solo sede **Chilca** en UI, filtros por defecto y datos demo nuevos |
| Campo `sede` | Se mantiene en BD, modelos y API (`chilca` / `auquimarca`) por compatibilidad y multi-sede futura |
| UI | **No** crear selectores visibles de sede |
| Seeders demo | **No** sembrar nuevos registros en Auquimarca |
| Esquema | **No** eliminar columnas ni validaciones `sede` existentes sin autorización explícita |
| Código nuevo | Fijar `sede = 'chilca'` con los helpers ya existentes |
| Fuera de alcance | Flask, `RiesgoAcademicoService` y lógica de riesgo académico |

### Helpers de referencia

- **Frontend:** `frontend/src/lib/sedeOperativa.js` — `SEDE_OPERATIVA`, `conSedeOperativa()`, `filtrosConSedeOperativa()`.
- **Backend:** `backend/app/Support/SedeOperativa.php` — `SedeOperativa::defaultConsulta()` en consultas donde la UI opera como sede única.

### Jerarquía

Si el DRS o un mockup asumen selector de sede o datos en Auquimarca, documentar la diferencia y aplicar esta decisión para cambios nuevos en V1.
