<?php
/**
 * Plugin Name: Log Login
 * Description: Plugin que requistra los logins de los usuarios
 * Author: Alejandro Martín Pérez
 * Version: 1.0
 */

require_once('functions.php');
register_activation_hook(__FILE__,'log_logins_activate');
register_deactivation_hook(__FILE__,'log_logins_desactivate');

//Añade un nuevo fichero CSS
add_action('admin_enqueue_scripts','loglogin_style');
function loglogin_style(){
        wp_enqueue_style('loglogin_style', 
        plugins_url().'/logLogin/main.css');
}

//Ejecuta el codigo cuando se activa el PlugIn
function log_logins_activate(){
    global $wpdb;
    $table_name = $wpdb-> prefix . 'log_logins';
    $sql = "CREATE TABLE $table_name (
        id int PRIMARY KEY AUTO_INCREMENT,
        user_id int NOT NULL,
        ip varchar(15) NOT NULL,
        web_browser varchar(20) NOT NULL,
        date_time datetime NOT NULL
    )";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql );
    add_option('logs_per_page', '10');
}

//Ejecuta el codigo cuando se desactiva el PlugIn
function log_logins_desactivate(){
    global $wpdb;
    $table_name = $wpdb-> prefix . 'log_logins';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    delete_option('logs_per_page');
}

// Inserta toda la información cada vez que alguien inicia sesión
add_action('wp_login', 'log_login', 10, 2);
function log_login($user_login, $user) {
    global $wpdb;
    $table_name = $wpdb-> prefix . 'log_logins';
    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user->ID,
            'date_time' => date("Y-m-d H:i:s"),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'web_browser' => get_navegador()
        ),
        array(
            '%d',
            '%s',
            '%s',
            '%s'
            )
    );
}
// Menú de configuración
add_action('admin_menu', 'log_logins_menu');
function log_logins_menu(){
    add_users_page(
        'Log Logins',          //Título del menú
        'Log Logins',          //Otro título
        'list_users',          //Permisos (capabilities)
        'log-logins-options',  //Identificador
        'log_logins_options'   //Función que mostrará la página de administración
    );
}

//Todo el contenido de la página de administración del PlugIn
function log_logins_options() {
    if (!current_user_can('list_users')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    //Comprueba el limitador guardado
    $delimiter = get_option("logs_per_page");
     if (isset($_POST['delimiter'])) {
         $delimiter = $_POST['delimiter'];
         update_option('logs_per_page', $delimiter);
    }
    
    define('NUM_ITEMS_BY_PAGE', $delimiter);
    echo '<h1>Log Logins</h1>';
    //Comprueba en que pagina estás y cuantas hay en total
    if (total_rows() > 0) {
        $page = false;
        if (isset($_GET['pages'])) {
            $page = $_GET['pages'];
        }
        if (!$page) {
            $start = 0;
            $page = 1;
        } else {
            $start = ($page - 1) * NUM_ITEMS_BY_PAGE;
        }
        $total_pages = ceil(total_rows() / NUM_ITEMS_BY_PAGE);
        if ($total_pages > 1) {
            echo '<h3>Página '.$page.' de ' .$total_pages .'</h3>';
        }
    }

    //Seleccionará todo el contenido que se requiere
        global $wpdb;
        $table_name = $wpdb-> prefix . 'log_logins';
        $r = $wpdb->get_results('select u.user_nicename, l.date_time, l.ip, l.web_browser from wp_users u inner join ' . $table_name  . ' l on u.id = l.user_id order by date_time desc limit '. $start . ', ' . NUM_ITEMS_BY_PAGE);
        ?>
        <!--Crea menú para seleccionar cual será el nuevo limite-->
            <form action="" method="post">
                <label>Limite de registros: </label>
                <select name="delimiter" id="delimiter">
                    <?php
                        for ($i=5; $i <= 100 ; $i=$i+5) { 
                        echo "<option value='$i'";
                            if ($i == NUM_ITEMS_BY_PAGE) {
                                echo 'selected="selected"';
                            }
                        echo">$i</option>";
                        }
                    ?>
                </select>
            <?php submit_button('Aplicar', 'small') ?>
        </form>
        <?php
        //Genera la tabla donde se muestra toda la información
        echo '<table style="text-align: center" class="widefat" id="logLoginTable"><tr><th>Nombre de usuario</th><th>Fecha</th><th>Dirección IP</th><th>Navegador Web</th></tr>';
        foreach ($r as $row) {
            echo '<tr><td>' . $row->user_nicename.'</td><td>' . $row->date_time . '</td><td>' . $row->ip.'</td><td><img src="' . get_icon_navegador($row->web_browser).'" width="32px" alt="'. $row->web_browser.'"/></td></tr>';
        }
        echo '<table>';
        //Genera la numeración de páginas
        echo '<nav>';
    echo '<ul class="pagination">';
    $href = get_site_url().'/wp-admin/users.php?page=log-logins-options&pages=';
    if ($total_pages > 1) {
        if ($page != 1) {
            echo '<li class="page-item"><a class="page-link" href="'. $href.($page-1).'"><span aria-hidden="true">&laquo;</span></a></li>';
        }
 
        for ($i=1;$i<=$total_pages;$i++) {
            if ($page == $i) {
                echo '<li class="page-item active"><a class="page-link" href="#">'.$page.'</a></li>';
            } else {
                echo '<li class="page-item"><a class="page-link" href="'.$href.$i.'">'.$i.'</a></li>';
            }
        }
 
        if ($page != $total_pages) {
            echo '<li class="page-item"><a class="page-link" href="'.$href.($page+1).'"><span aria-hidden="true">&raquo;</span></a></li>';
        }
    }
    echo '</ul>';
echo '</nav>';
        
}

?>