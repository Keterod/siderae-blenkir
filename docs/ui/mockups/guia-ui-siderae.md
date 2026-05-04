# Guía UI SIDERAE-Blenkir

## 1. Objetivo de la guía

Esta guía orienta el diseño visual de los mockups y de las futuras pantallas de SIDERAE-Blenkir: paleta, layout, componentes y criterios de calidad. No define ni altera la lógica funcional del sistema; solo documenta la referencia visual para mantener coherencia institucional y usabilidad académica.

---

## 2. Módulos principales considerados en el diseño

La guía visual se basa en los módulos principales definidos para SIDERAE-Blenkir:

- Autenticación
- Dashboard académico
- Gestión de estudiantes
- Registro / edición de estudiantes
- Perfil del estudiante
- Registro de notas
- Registro de asistencia
- Variables socioeconómicas
- Procesamiento de riesgo académico
- Alertas
- Detalle de alerta
- Intervenciones
- Reportes

El objetivo visual es establecer una base de diseño clara, moderna e institucional para los mockups y futuras pantallas del sistema, independientemente del avance específico de cada sprint.

---

## 3. Concepto visual

SIDERAE-Blenkir debe verse como una plataforma institucional de analítica académica para el Colegio Blenkir: orden, claridad y confianza en la navegación, con énfasis en seguimiento de estudiantes, indicadores, riesgo académico, alertas e intervenciones, sin perder un tono profesional y acorde a un entorno educativo.

---

## 4. Paleta de colores oficial

| Nombre | Código | Uso |
| --- | --- | --- |
| Primary / Naranja SIDERAE | `#F05A0E` | Acciones principales, marca, botones destacados, menú activo |
| Secondary / Naranja claro | `#F47C2A` | Hover, acentos secundarios, estados de atención |
| Tertiary / Naranja oscuro | `#C94A0C` | Estados activos, énfasis fuerte, bordes destacados |
| Neutral cálido | `#88726B` | Texto secundario, iconos neutros |
| Azul institucional | `#1E63B5` | Enlaces, acciones secundarias, navegación de apoyo |
| Verde éxito | `#2FAF7B` | Riesgo bajo, alerta cerrada, confirmaciones |
| Rojo alerta | `#DC2626` | Riesgo alto, errores, alertas críticas |
| Amarillo advertencia | `#F59E0B` | Riesgo medio, alerta en atención, advertencias |
| Fondo general | `#F2F2F2` | Fondo de la aplicación |
| Blanco | `#FFFFFF` | Tarjetas, paneles, formularios |
| Borde gris | `#CCCCCC` | Inputs, separadores, bordes suaves |
| Texto principal | `#333333` | Títulos y contenido principal |

**Uso del naranja principal:** aplicar `#F05A0E` como acento (botón principal, menú activo, acciones destacadas), no como fondo masivo de toda la pantalla. `#F47C2A` para hover y acentos secundarios; `#C94A0C` para estados activos o énfasis fuerte.

---

## 5. Variables CSS sugeridas

```css
:root {
  --primary: #F05A0E;
  --primary-light: #F47C2A;
  --primary-dark: #C94A0C;

  --secondary: #1E63B5;
  --success: #2FAF7B;
  --warning: #F59E0B;
  --danger: #DC2626;

  --background: #F2F2F2;
  --surface: #FFFFFF;
  --border: #CCCCCC;
  --text: #333333;
  --muted: #88726B;
}
```

---

## 6. Estructura general de interfaz

### Sidebar lateral

- Logo o nombre SIDERAE.
- Subtítulo `Blenkir Analytics`.
- Navegación principal:
  - Dashboard
  - Estudiantes
  - Alertas
  - Intervenciones
  - Reportes
  - Configuración
- Elemento activo resaltado con naranja institucional.

### Header superior

- Buscador global cuando aplique.
- Ícono de notificaciones.
- Ícono de ayuda o información.
- Usuario autenticado.
- Rol del usuario si está disponible.
- Opción de cerrar sesión o menú de usuario.

### Contenido principal

- Título de pantalla.
- Descripción breve.
- Filtros si aplica.
- Tarjetas KPI si aplica.
- Tablas o formularios.
- Acciones principales.

**Estilo base del layout:** fondo general claro, sidebar lateral izquierdo, tarjetas blancas, bordes suaves, sombras ligeras, tablas limpias y filtros compactos cuando corresponda.

---

## 7. Mockups disponibles

Cada imagen tiene una descripción en Markdown correspondiente en la misma carpeta (`docs/ui/mockups/`):

- `01-login.png` / `01-login.md`
- `02-dashboard.png` / `02-dashboard.md`
- `03-listado-estudiantes.png` / `03-listado-estudiantes.md`
- `04-registro-edicion-estudiante.png` / `04-registro-edicion-estudiante.md`
- `05-perfil-estudiante.png` / `05-perfil-estudiante.md`
- `06-registro-notas.png` / `06-registro-notas.md`
- `07-registro-asistencia.png` / `07-registro-asistencia.md`
- `08-variables-socioeconomicas.png` / `08-variables-socioeconomicas.md`
- `09-riesgo-academico.png` / `09-riesgo-academico.md`
- `10-listado-alertas.png` / `10-listado-alertas.md`
- `11-detalle-alerta.png` / `11-detalle-alerta.md`
- `12-registro-intervencion.png` / `12-registro-intervencion.md`

---

## 8. Uso correcto de los mockups

- Los mockups son **referencia visual**, no especificación de datos reales.
- Los nombres, fechas, porcentajes, identificadores y cantidades que aparecen en ellos son **datos de ejemplo**.
- Esos datos de ejemplo **no deben tratarse como datos reales** en implementación ni en documentación operativa.
- La interfaz implementada debe consumir datos reales desde las APIs y el estado que defina el proyecto cuando corresponda.
- No debe inventarse información en pantalla que no provenga del backend o del estado real de la aplicación.
- Si un dato visual del mockup **no existe aún** en el sistema, debe dejarse como pendiente o implementarse solo cuando el sprint correspondiente lo defina, usando estado vacío o placeholder controlado cuando sea adecuado.

---

## 9. Componentes visuales principales

### Sidebar

- Menú lateral persistente.
- Estado activo claramente visible.
- Íconos por módulo.
- Diseño claro y no saturado.

### Header

- Buscador global (cuando aplique).
- Notificaciones.
- Ayuda o información.
- Usuario activo.
- Menú de sesión o cerrar sesión.

### Tarjetas KPI

- Indicadores numéricos.
- Ícono asociado.
- Color de estado cuando corresponda.
- Texto descriptivo breve.
- Estilo: superficie blanca, borde suave, sombra ligera; valor destacado y jerarquía legible.

### Tablas

- Encabezados claros.
- Filas con separación legible.
- Acciones visibles (por ejemplo enlaces o botones de acción secundaria en azul institucional, según la pantalla).
- Badges de estado (riesgo, alerta, etc.).
- Paginación cuando aplique.

### Formularios

- Etiquetas claras.
- Campos obligatorios marcados con `*`.
- Mensajes de validación visibles.
- Botón principal naranja.
- Botón cancelar neutro.

### Badges de riesgo

- Bajo
- Medio
- Alto

### Badges de alerta

- Pendiente
- En atención
- Cerrada

### Botones

- Principal (naranja institucional).
- Secundario (p. ej. azul institucional o variante definida para acciones secundarias).
- Cancelar (neutro).
- Acción crítica (coherente con rojo alerta cuando corresponda).
- Acción de éxito (coherente con verde éxito cuando corresponda).

### Mensajes

- Error
- Éxito
- Advertencia
- Estado vacío (cuando no hay datos que mostrar)

---

## 10. Estados visuales

### Riesgo académico

- **Bajo:** verde `#2FAF7B`
- **Medio:** amarillo/naranja `#F59E0B` o `#F05A0E`
- **Alto:** rojo `#DC2626`

### Alertas

- **Pendiente:** naranja institucional `#F05A0E`
- **En atención:** amarillo o naranja
- **Cerrada:** verde

### Intervenciones

- **Pendiente:** rojo suave
- **En progreso:** amarillo/naranja
- **Completada:** verde

### Formularios

- **Campo válido:** borde neutro o verde suave
- **Campo inválido:** borde rojo
- **Campo obligatorio:** marcado con `*`
- **Campo deshabilitado:** gris suave

Los estados deben ser distinguibles **por color y por texto** (etiqueta o leyenda), no solo por color.

---

## 11. Reglas para implementación futura en Cursor

- No modificar el backend salvo que un sprint lo pida explícitamente.
- No cambiar endpoints sin acuerdo explícito del alcance.
- No romper autenticación.
- No romper permisos.
- No romper el procesamiento de riesgo académico.
- No romper alertas.
- No romper intervenciones.
- No romper pruebas existentes.
- No inventar módulos nuevos fuera de lo cubierto por esta guía y los mockups.
- No usar datos de ejemplo de los mockups como datos reales.
- Priorizar componentes reutilizables.
- Implementar visualmente según mockups y esta guía, manteniendo compatibilidad con la lógica ya existente.
- Si falta información en backend o en estado, dejarla como pendiente o usar estado vacío controlado.
- Cualquier cambio funcional debe acordarse y documentarse antes de implementarse.

---

## 12. Alcance del rediseño UI

**Se permite modificar:**

- Componentes React.
- Estilos.
- Layout.
- Sidebar.
- Header.
- Tarjetas.
- Tablas.
- Formularios.
- Badges.
- Organización visual de módulos en pantalla.
- Estados visuales.
- Mensajes de error y éxito.
- Experiencia responsive básica.

**No se debe modificar:**

- Migraciones.
- Modelos Laravel.
- Controladores backend.
- Rutas backend.
- Lógica de procesamiento de riesgo.
- Lógica de alertas.
- Lógica de intervenciones.
- Seeders de permisos.
- Pruebas ya aprobadas.
- Configuración Docker.
- Variables de entorno.

---

## 13. Prioridad de implementación UI

Orden recomendado para un rediseño progresivo alineado a los mockups:

1. Layout general, sidebar y header  
2. Login  
3. Dashboard  
4. Listado de estudiantes  
5. Registro / edición de estudiante  
6. Perfil del estudiante  
7. Notas, asistencia y variables socioeconómicas  
8. Riesgo académico  
9. Alertas  
10. Detalle de alerta  
11. Registro de intervención  

El rediseño debe implementarse de forma progresiva para evitar romper funcionalidades existentes.

---

## 14. Criterios de calidad visual

### Usabilidad

- Navegación clara.
- Acciones visibles.
- Formularios simples.
- Mensajes comprensibles.
- Filtros accesibles.
- Estados vacíos claros.

### Consistencia

- Mismos estilos de botones.
- Mismas tarjetas.
- Mismas tablas.
- Mismos badges.
- Misma jerarquía visual.
- Uso coherente de colores de la paleta oficial.

### Accesibilidad básica

- Contraste suficiente.
- Textos legibles.
- Botones claros.
- Estados distinguibles por color y texto.
- Tamaños adecuados para lectura.

### Trazabilidad

- Fechas visibles cuando aplique.
- Estados visibles.
- Responsable visible cuando aplique.
- Historial de riesgo accesible donde el diseño lo prevea.
- Historial de alertas accesible donde el diseño lo prevea.
- Historial de intervenciones accesible donde el diseño lo prevea.

### Mantenibilidad

- Componentes reutilizables.
- Separación clara de responsabilidades en el front.
- Estilos organizados.
- Evitar duplicación innecesaria.
- No mezclar datos de ejemplo con lógica ni estado real.

---

## 15. Resumen final

SIDERAE-Blenkir debe verse como una plataforma institucional de analítica académica para el Colegio Blenkir, con estética limpia, moderna y profesional. Debe usar el naranja `#F05A0E` como identidad principal, apoyarse en tarjetas blancas, fondo gris claro, navegación ordenada y estados visuales claros para riesgo, alertas e intervenciones, siempre alineado a los mockups y a esta guía como referencia estable.
