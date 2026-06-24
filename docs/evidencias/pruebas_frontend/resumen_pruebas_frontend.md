# Resumen de Pruebas Frontend - SIDERAE-Blenkir

## Estado General
- **Total de archivos de prueba:** 12
- **Pruebas Mapeadas al Core:** 5 (Registro de Notas, Asistencia, Excel Import/Export, Dashboard y Alertas)
- **Resultado:** ⚠️ Parcialmente PASS (Mocks funcionan, flujos reales de autenticación fallan por falta de backend)

## Detalles de Ejecución
- **Herramienta:** Cypress
- **Entorno:** Servidor de desarrollo local (`npm run dev` en `http://localhost:5173`)

## Pruebas Exitosas (Mockeadas - 100% PASS)
Estas pruebas funcionan correctamente aislando el frontend mediante la interceptación de respuestas HTTP (`cy.intercept`):
1. `alertas_intervenciones.cy.js`
2. `asistencia_registro.cy.js`
3. `dashboard_filtros_graficos.cy.js`
4. `excel_import_export.cy.js`
5. `notas_registro.cy.js`

## Pruebas Fallidas
Las siguientes pruebas fallan porque dependen de que el backend real de Laravel esté respondiendo (`localhost:8000`), lo cual no ocurre de forma estable en el entorno de testing actual, o los tiempos de espera exceden el límite:
1. `auth-login.cy.js`
2. `debug-login2.cy.js`
3. `logout.cy.js`
4. `rf04-reportes-conductuales.cy.js`
5. `smoke-navegacion-v1.cy.js`
6. `smoke-perfil-estudiante-nc11.cy.js`

## Archivos de Evidencia
- Log de ejecución de Cypress: `docs/evidencias/pruebas_frontend/resultado_cypress_frontend.txt`
- Capturas de pantalla de flujos PASS: `docs/evidencias/pruebas_frontend/capturas/`

## Conclusión
El frontend cuenta con pruebas sólidas para los flujos principales (Notas, Asistencia, Dashboard, Alertas, Importación Excel) que validan el comportamiento de la UI de forma aislada. Las pruebas E2E completas que dependen de una instancia del backend activa requerirían levantar el entorno completo de SIDERAE (idealmente usando los contenedores Docker configurados) para su correcta ejecución.
