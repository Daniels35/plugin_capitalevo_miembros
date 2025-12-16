# 👥 DS Team Members

**Gestor de perfiles de equipo con efectos interactivos.**

Este plugin crea un nuevo tipo de contenido ("Team Members") en WordPress para administrar los perfiles de los miembros de tu organización. En el frontend, despliega una grilla elegante de tarjetas; al hacer clic en un miembro, se activa una ventana modal con un **efecto de escritura tipo máquina (Typewriter)** usando la librería *Typed.js*, o se redirige a una URL personalizada si se prefiere.

## 📋 Características Principales

### 🛠️ Gestión de Contenido (Backend)
* **Custom Post Type:** Registra automáticamente el menú "Team Members" en el panel de administración, permitiendo añadir miembros como si fueran entradas.
* **Campos Personalizados (Meta Boxes):** Interfaz intuitiva para ingresar datos adicionales sin complicaciones:
    * **Cargo/Posición:** Título profesional.
    * **Email:** Correo de contacto visible en la tarjeta.
    * **Texto para Typed.js:** El párrafo que se animará letra por letra dentro del modal.
    * **URL de Redirección:** Opción para saltarse el modal y llevar al usuario a otra página (ej. perfil de LinkedIn o bio completa).

### 🎨 Experiencia Visual (Frontend)
* **Efecto "Typed":** Integra la librería `Typed.js` vía CDN. Al abrir el modal, la biografía del miembro se "escribe" en tiempo real, creando un efecto dinámico y moderno.
* **Lógica Condicional:** El script detecta automáticamente si el perfil tiene una "URL de Redirección". Si existe, el clic navega a esa URL; si no, abre el modal informativo.
* **Diseño Responsivo:** Grilla flexible (Flexbox) que se adapta de 3 columnas en escritorio a 1 columna en móviles, con estilos de modal totalmente adaptables.

## ⚙️ Instrucciones de Uso

1.  **Añadir Miembro:**
    * Ve a **Team Members > Añadir Nuevo**.
    * Escribe el **Nombre** en el título principal.
    * Sube la **Imagen Destacada** (Foto del perfil).
    * Llena los campos de "Información del Miembro" (Cargo, Email, Texto Typed o URL).
2.  **Publicar:** Guarda el perfil.
3.  **Mostrar en la Web:** Inserta el shortcode en cualquier página.

## 📂 Estructura del Plugin

* `ds-team-members.php`: Archivo principal. Registra el CPT, los Meta Boxes, el Shortcode y encola los recursos.
* `ds-team-section.js`: Controla la interacción del usuario (clic en tarjeta), la lógica de redirección y la inicialización de la animación Typed.js.
* `ds-team-section.css`: Estilos para las tarjetas, la grilla responsive y la ventana modal (popup).

## 🚀 Instalación

1.  Sube la carpeta del plugin al directorio `/wp-content/plugins/`.
2.  Activa el plugin desde el panel de WordPress.
3.  Comienza a crear perfiles desde el nuevo menú "Team Members".

## 💻 Shortcode

Para desplegar la sección completa del equipo en cualquier parte de tu sitio:

```shortcode
[ds_team_members]
