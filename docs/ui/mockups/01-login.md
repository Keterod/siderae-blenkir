# Mockup 01 — Login

## Objetivo de la pantalla

Permitir el acceso seguro al sistema SIDERAE-Blenkir mediante correo institucional y contraseña.

Esta pantalla debe transmitir una imagen institucional, limpia y profesional. Es la primera interacción del usuario con el sistema, por lo tanto debe mostrar claramente la identidad de SIDERAE-Blenkir y mantener una experiencia simple.

---

## Elementos visibles

- Fondo general con tono claro cálido.
- Tarjeta central de inicio de sesión.
- Ícono superior relacionado con analítica o crecimiento.
- Nombre del sistema: `SIDERAE-Blenkir`.
- Subtítulo: `Sistema Inteligente de Detección Temprana de Riesgo Académico`.
- Caja de error de autenticación.
- Mensaje de error:
  - `Error de autenticación`
  - `Las credenciales ingresadas son incorrectas. Por favor, verifique e intente nuevamente.`
- Campo de correo institucional.
- Ícono de correo dentro del input.
- Placeholder de ejemplo: `ejemplo@institucion.edu.mx`.
- Campo de contraseña.
- Ícono de candado dentro del input.
- Enlace: `¿Olvidó su contraseña?`
- Botón principal: `Iniciar Sesión`.
- Ícono de flecha dentro del botón.
- Texto inferior: `Acceso restringido a personal autorizado.`

---

## Distribución visual

La pantalla usa una composición centrada:

- El fondo ocupa toda la pantalla.
- La tarjeta de login se ubica al centro horizontal y vertical.
- Dentro de la tarjeta, los elementos están organizados en columna:
  1. Ícono institucional.
  2. Nombre del sistema.
  3. Descripción breve.
  4. Mensaje de error.
  5. Campo de correo.
  6. Campo de contraseña.
  7. Enlace de recuperación.
  8. Botón de inicio de sesión.
  9. Mensaje informativo inferior.

La tarjeta tiene bordes suaves, fondo blanco y una separación visual clara entre secciones.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores principales

- Fondo general: tono claro derivado de `#F2F2F2` con matiz cálido.
- Tarjeta: `#FFFFFF`.
- Color principal del botón: naranja oscuro/institucional, basado en `#C94A0C` o `#F05A0E`.
- Ícono superior: naranja institucional `#F05A0E`.
- Texto principal: `#333333`.
- Bordes de inputs: gris medio `#CCCCCC`.
- Mensaje de error:
  - fondo rojo claro
  - borde o barra lateral roja
  - texto rojo `#DC2626`.

### Estilo

- Diseño minimalista.
- Tarjeta centrada.
- Bordes ligeramente redondeados.
- Botón ancho y visible.
- Inputs de altura cómoda.
- Tipografía limpia y legible.
- Jerarquía visual clara entre título, subtítulo, formulario y mensajes.

---

## Comportamiento esperado

- El usuario ingresa correo y contraseña.
- Al presionar `Iniciar Sesión`, el sistema debe enviar las credenciales al backend.
- Si las credenciales son correctas:
  - debe iniciar sesión
  - debe redirigir al dashboard principal.
- Si las credenciales son incorrectas:
  - debe mostrarse el mensaje de error visible en el mockup.
- El enlace `¿Olvidó su contraseña?` puede quedar como enlace visual o funcionalidad futura si aún no está implementado.
- El botón debe mostrar estado de carga mientras se procesa la autenticación.
- No debe permitir envío vacío si los campos obligatorios están incompletos.

---

## Relación con el sistema actual

Esta pantalla corresponde al módulo de autenticación implementado en Sprint 2.

Debe conectarse con:

- Laravel Sanctum.
- Endpoint de login existente.
- Manejo de sesión actual.
- Contexto de autenticación del frontend.
- Redirección al dashboard o pantalla principal después del login.

---

## Observaciones para Cursor

- Rediseñar únicamente la interfaz del login existente.
- No modificar la lógica de autenticación que ya funciona.
- No cambiar endpoints.
- No romper Sanctum ni el manejo de cookies.
- Mantener el flujo actual de login/logout.
- Usar los colores institucionales del sistema.
- El correo mostrado en el mockup es solo referencial; no debe quemarse como dato fijo.
- El mensaje de error debe mostrarse dinámicamente solo cuando falle el login.
- Mantener compatibilidad con el usuario de prueba actual:
  - `test@example.com`
  - `password`
- Si el enlace `¿Olvidó su contraseña?` no existe en backend, dejarlo como enlace visual sin romper la pantalla.