<?php
/*
Plugin Name: DS Team Members
Plugin URI: https://tagdigital.com.co/
Description: Plugin que crea un tipo de contenido personalizado para miembros de equipo, con shortcode para mostrar el bloque y modal con Typed.js
Version: 1.0
Author: Daniel Diaz
Author URI: https://www.linkedin.com/in/danielsdiaz35/
License: GPL2
Text Domain: ds-team-members
*/

/*
 * En memoria: 
 *   - LinkedIn: https://www.linkedin.com/in/danielsdiaz35/
 *   - Tag Digital: https://tagdigital.com.co/
 *
 * ¡Gracias por usar este plugin!
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evitamos acceso directo
}

class DSTeamMembers {

    public function __construct() {
        // 1. Registramos el Custom Post Type
        add_action('init', array($this, 'register_custom_post_type'));

        // 2. Añadimos meta boxes para campos extra
        add_action('add_meta_boxes', array($this, 'add_team_member_metaboxes'));
        add_action('save_post', array($this, 'save_team_member_meta'), 10, 2);

        // 3. Shortcode para mostrar los miembros
        add_shortcode('ds_team_members', array($this, 'render_team_members_shortcode'));

        // 4. Encolamos scripts y estilos (Typed.js, CSS, etc.)
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * 1. Registrar el Custom Post Type "team_member"
     */
    public function register_custom_post_type() {
        $labels = array(
            'name'               => 'Team Members',
            'singular_name'      => 'Team Member',
            'add_new'            => 'Añadir Nuevo',
            'add_new_item'       => 'Añadir Nuevo Miembro',
            'edit_item'          => 'Editar Miembro',
            'new_item'           => 'Nuevo Miembro',
            'view_item'          => 'Ver Miembro',
            'search_items'       => 'Buscar Miembros',
            'not_found'          => 'No se encontraron miembros',
            'not_found_in_trash' => 'No se encontraron miembros en la papelera',
            'menu_name'          => 'Team Members'
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => false,
            'rewrite'            => array('slug' => 'team-member'),
            'supports'           => array('title', 'editor', 'thumbnail'), 
            'menu_icon'          => 'dashicons-groups',
        );

        register_post_type('team_member', $args);
    }

    /**
     * 2. Agregar meta boxes para campos adicionales (posición, email, texto typed, URL de redirección)
     */
    public function add_team_member_metaboxes() {
        add_meta_box(
            'ds_team_member_info',
            'Información del Miembro',
            array($this, 'render_team_member_metabox'),
            'team_member',
            'normal',
            'high'
        );
    }

    public function render_team_member_metabox($post) {
        // Campos
        $position    = get_post_meta($post->ID, '_ds_team_member_position', true);
        $email       = get_post_meta($post->ID, '_ds_team_member_email', true);
        $typed_text  = get_post_meta($post->ID, '_ds_team_member_typed_text', true);
        $redirect_url= get_post_meta($post->ID, '_ds_team_member_redirect_url', true);

        // Nonce
        wp_nonce_field('ds_team_member_nonce_action', 'ds_team_member_nonce_field');
        ?>
        <p>
            <label for="ds_team_member_position">Posición/Cargo:</label><br>
            <input type="text" id="ds_team_member_position" name="ds_team_member_position" 
                   value="<?php echo esc_attr($position); ?>" style="width: 100%;">
        </p>
        <p>
            <label for="ds_team_member_email">Email:</label><br>
            <input type="email" id="ds_team_member_email" name="ds_team_member_email" 
                   value="<?php echo esc_attr($email); ?>" style="width: 100%;">
        </p>
        <p>
            <label for="ds_team_member_redirect_url">URL de Redirección (opcional):</label><br>
            <input type="text" id="ds_team_member_redirect_url" name="ds_team_member_redirect_url" 
                   value="<?php echo esc_attr($redirect_url); ?>" style="width: 100%;">
            <small>Si este campo se completa, al hacer clic se redireccionará en lugar de mostrar el modal.</small>
        </p>
        <p>
            <label for="ds_team_member_typed_text">Texto para Typed.js (solo se usa si NO hay redirección):</label><br>
            <textarea id="ds_team_member_typed_text" name="ds_team_member_typed_text" rows="6" style="width: 100%;"><?php echo esc_textarea($typed_text); ?></textarea>
        </p>
        <?php
    }

    public function save_team_member_meta($post_id, $post) {
        // Verificamos nonce
        if (!isset($_POST['ds_team_member_nonce_field']) || 
            !wp_verify_nonce($_POST['ds_team_member_nonce_field'], 'ds_team_member_nonce_action')) {
            return;
        }
        // Verificamos auto-save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        // Verificamos permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Guardar campos
        if (isset($_POST['ds_team_member_position'])) {
            update_post_meta($post_id, '_ds_team_member_position', sanitize_text_field($_POST['ds_team_member_position']));
        }
        if (isset($_POST['ds_team_member_email'])) {
            update_post_meta($post_id, '_ds_team_member_email', sanitize_text_field($_POST['ds_team_member_email']));
        }
        if (isset($_POST['ds_team_member_typed_text'])) {
            update_post_meta($post_id, '_ds_team_member_typed_text', wp_kses_post($_POST['ds_team_member_typed_text']));
        }
        if (isset($_POST['ds_team_member_redirect_url'])) {
            update_post_meta($post_id, '_ds_team_member_redirect_url', esc_url_raw($_POST['ds_team_member_redirect_url']));
        }
    }

    /**
     * 3. Shortcode [ds_team_members]
     *    - Consulta los team_members y genera el HTML + modal
     */
    public function render_team_members_shortcode($atts) {
        // Consulta de miembros
        $args = array(
            'post_type'      => 'team_member',
            'posts_per_page' => -1,
            'order'          => 'ASC',
            'orderby'        => 'date'
        );
        $query = new WP_Query($args);

        if(!$query->have_posts()) {
            return '<p>No hay miembros del equipo en este momento.</p>';
        }

        // Estructura base
        ob_start();
        ?>
        <div class="team-section">
            <?php while($query->have_posts()): $query->the_post(); 
                $post_id      = get_the_ID();
                $name         = get_the_title();
                $position     = get_post_meta($post_id, '_ds_team_member_position', true);
                $email        = get_post_meta($post_id, '_ds_team_member_email', true);
                $typed_text   = get_post_meta($post_id, '_ds_team_member_typed_text', true);
                $redirect_url = get_post_meta($post_id, '_ds_team_member_redirect_url', true);

                // Imagen destacada
                $thumbnail_url = '';
                if(has_post_thumbnail($post_id)) {
                    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
                }
                ?>
                <div class="team-member" 
                     data-member="<?php echo esc_attr($post_id); ?>">
                    <?php if($thumbnail_url): ?>
                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($name); ?>">
                    <?php endif; ?>
                    <h3><?php echo esc_html($name); ?></h3>
                    <?php if($position): ?>
                        <p><?php echo esc_html($position); ?></p>
                    <?php endif; ?>
                    <?php if($email): ?>
                        <p class="email-members"><?php echo esc_html($email); ?></p>
                    <?php endif; ?>
                </div>
                <?php 
                // Almacenamos datos en un array para pasarlo luego a JS
                $members_data[$post_id] = array(
                    'name'        => $name,
                    'typedText'   => $typed_text,
                    'redirectUrl' => $redirect_url
                );
            endwhile; ?>
        </div>

        <!-- Modal -->
        <div id="ds-member-modal" class="ds-modal">
            <div class="ds-modal-content">
                <span class="ds-close">&times;</span>
                <h2 id="ds-modal-member-name"></h2>
                <div class="ds-container-typed">
                    <div id="ds-typed"></div>
                </div>
            </div>
        </div>
        <?php
        wp_reset_postdata();

        // Localizar el array con la info de cada miembro para usarlo en JS
        if(!empty($members_data)) {
            wp_localize_script('ds-team-section-js', 'dsTeamMembersData', $members_data);
        }

        return ob_get_clean();
    }

    /**
     * 4. Encolar scripts y estilos
     */
    public function enqueue_scripts() {
        // CSS principal
        wp_register_style('ds-team-section-css', plugin_dir_url(__FILE__).'ds-team-section.css', array(), '1.0');
        wp_enqueue_style('ds-team-section-css');

        // Typed.js (CDN)
        wp_register_script('typed-js', 'https://cdn.jsdelivr.net/npm/typed.js@2.0.12', array(), '2.0.12', true);
        wp_enqueue_script('typed-js');

        // JS principal
        wp_register_script('ds-team-section-js', plugin_dir_url(__FILE__).'ds-team-section.js', array('jquery','typed-js'), '1.0', true);
        wp_enqueue_script('ds-team-section-js');
    }
}

new DSTeamMembers();
