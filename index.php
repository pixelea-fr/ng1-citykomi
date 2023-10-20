<?php

class CityKomiPlugin {
  private $api_key;
  private $key_signature;
  private $site_url;
  private $canaux;
  private $enable_cache;
  private $cache_delay;

  public function __construct() {

      $this->api_key = get_option('citykomi_key_api');
      $this->key_signature = get_option('citykomi_key_signature_key');
      $this->site_url = get_option('citykomi_url');
      $this->canaux= get_option('citykomi_canaux');
      $this->enable_cache= get_option('citykomi_enable_cache');
      $this->cache_delay= get_option('citykomi_cache_delay');

      add_action('wp_enqueue_scripts',  array($this, 'load_styles'), 103);
      add_action('admin_menu', array($this, 'add_admin_page'));
      add_action('admin_init', array($this, 'register_settings'));

      // Ajoutez un shortcode pour effectuer une requête CityKomi personnalisée
      add_shortcode('citykomi', array($this, 'citykomi_shortcode'));
  }
  public function load_styles() {
    $plugin_url = plugins_url('', __FILE__);
    wp_enqueue_style('citykomi',$plugin_url . '/assets/css/style.css', array(), null, 'all');

}   
  public function add_admin_page() {
      add_menu_page(
          'CityKomi Options',
          'CityKomi',
          'manage_options',
          'citykomi_settings',
          array($this, 'create_admin_page'),
          'dashicons-admin-generic'
      );
  }

  public function create_admin_page() {
      // Vérifiez si l'utilisateur actuel a la capacité de gérer les options
      if (!current_user_can('manage_options')) {
          wp_die(__('You do not have sufficient permissions to access this page.'));
      }

      ?>
      <div class="wrap">
          <h2>CityKomi Settings</h2>
          <form method="post" action="options.php">
              <?php
              settings_fields('citykomi_settings_group');
              do_settings_sections('citykomi_settings');
              submit_button();
              ?>
          </form>
      </div>
      <?php
  }

  public function register_settings() {
      // Enregistrez les options dans la base de données
      register_setting('citykomi_settings_group', 'citykomi_url');
      register_setting('citykomi_settings_group', 'citykomi_key_api');
      register_setting('citykomi_settings_group', 'citykomi_key_signature_key');
      register_setting('citykomi_settings_group', 'citykomi_canaux');
      register_setting('citykomi_settings_group', 'citykomi_enable_cache');
      register_setting('citykomi_settings_group', 'citykomi_cache_delay');

      // Ajoutez des sections et des champs pour chaque option
      add_settings_section('citykomi_settings_section', 'CityKomi Settings', null, 'citykomi_settings');

      add_settings_field('citykomi_url', 'CityKomi URL', array($this, 'citykomi_url_callback'), 'citykomi_settings', 'citykomi_settings_section');
      add_settings_field('citykomi_canaux', 'CityKomi canaux à utiliser', array($this, 'citykomi_canaux_callback'), 'citykomi_settings', 'citykomi_settings_section');
      add_settings_field('citykomi_key_api', 'CityKomi API Key', array($this, 'citykomi_key_api_callback'), 'citykomi_settings', 'citykomi_settings_section');
      add_settings_field('citykomi_key_signature_key', 'CityKomi Signature Key', array($this, 'citykomi_key_signature_key_callback'), 'citykomi_settings', 'citykomi_settings_section');
      add_settings_field('citykomi_enable_cache', 'CityKomi activer le cache (transients)', array($this, 'citykomi_enable_cache_callback'), 'citykomi_settings', 'citykomi_settings_section');
      add_settings_field('citykomi_cache_delay', 'CityKomi Durée du cache en secondes', array($this, 'citykomi_cache_delay_callback'), 'citykomi_settings', 'citykomi_settings_section');
    }

  public function citykomi_url_callback() {
      $value = get_option('citykomi_url');
      echo "<input type='text' name='citykomi_url' value='$value' class='regular-text' />";
  }

  public function citykomi_key_api_callback() {
      $value = get_option('citykomi_key_api');
      echo "<input type='text' name='citykomi_key_api' value='$value' class='regular-text' />";
  }

  public function citykomi_key_signature_key_callback() {
      $value = get_option('citykomi_key_signature_key');
      echo "<input type='text' name='citykomi_key_signature_key' value='$value' class='regular-text' />";
  }
  public function citykomi_cache_delay_callback() {
    $value = get_option('citykomi_cache_delay');
    if(empty($value)){
        $value = 1 * HOUR_IN_SECONDS;
    }
    echo "<input type='text' name='citykomi_cache_delay' value='$value' class='regular-text' />";
}
public function citykomi_enable_cache_callback() {
    $value = get_option('citykomi_enable_cache');
    $checked = $value ? 'checked="checked"' : '';

    echo "<label for='citykomi_enable_cache'>
              <input type='checkbox' id='citykomi_enable_cache' name='citykomi_enable_cache' value='1' $checked />
              Enable Caching
          </label>";
}
public function citykomi_canaux_callback() {
    $value = get_option('citykomi_canaux');
    $canaux = json_decode($this->sendRequest('/v1/public/partners/channels/179?generate_qrcode=false'), true);
    
    echo "<select id='citykomi_canaux' name='citykomi_canaux[]' multiple>";

    if (array_key_exists('data', $canaux) && !empty($canaux['data'])) {
        foreach ($canaux['data'] as $item) {
            $name = $item['name'];
            $id = $item['id'];
            $status = $item['status'];

            if ($status == 1) {
                $selected = in_array($id, $value) ? 'selected' : '';
                echo "<option value='$id' $selected>$name</option>";
            }
        }
    }

    echo "</select>";
}



  public function sendRequest($route) {

    $delay_cache = $this->cache_delay;
    $enable_cache = $this->enable_cache;
    //  l'URL complet de la route (si nécessaire)
    $full_url = $this->site_url . $route;
    $transient_key = 'donnees_' . md5($full_url);
    //delete_transient($transient_key);
    $notFoundData = '{"name":"NotFoundError","message":"Not found","code":404,"type":"NOT_FOUND"}';
    if ($enable_cache) {
        // Si la mise en cache est activée, essayez de récupérer les données du transient
        $cached_data = get_transient($transient_key);
        if (false !== $cached_data) {
            // Les données sont en cache, retournez-les directement
            return $cached_data;
        }
    } else {
        // Si la mise en cache est désactivée, supprimez le transient existant (s'il existe)
        delete_transient($transient_key);
    }

      $timestamp = round(microtime(true) * 1000);
      $data = "GET" . $timestamp . $route;
      $api_signature = hash_hmac('sha256', $data, $this->key_signature);

      $curl = curl_init();

      curl_setopt_array($curl, array(
          CURLOPT_URL => $this->site_url . $route,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
              'api-signature-timestamp: ' . $timestamp,
              'api-signature: ' . $api_signature,
              'Authorization: Bearer ' . $this->api_key
          ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      if($enable_cache && $response  !== $notFoundData){
        set_transient($transient_key, $response, 1 * $delay_cache); 
      }
      return $response;
  }
    public function citykomi_shortcode($atts) {
        // Paramètres par défaut
        $atts = shortcode_atts(array(
            'request' => '',
            'view' => 'simple',
        ), $atts);

        // Récupérez les paramètres du shortcode
        $route = $atts['request'];
        $view = $atts['view'];

        // Effectuez la requête CityKomi avec les paramètres fournis
        $response = json_decode($this->sendRequest($route),true);

        // Traitez la réponse en fonction du paramètre "view"
        if (is_file(__DIR__ . '/view/' . $view . '.php')) {
        include_once(__DIR__ . '/view/' . $view . '.php');
    } else {
        // Traitez la réponse de manière différente selon d'autres vues
        include_once(__DIR__ . '/view/default.php');
    }

        return ;
    }
    public static function transformeEnParagraphes($texte) {
        // Divise le texte en lignes en utilisant "\n" comme séparateur
        $lignes = explode("\n", $texte);
    
        // Crée une chaîne pour stocker le texte formaté en paragraphes
        $texteFormate = "";
    
        // Parcours chaque ligne et ajoute une balise de paragraphe si la ligne n'est pas vide
        foreach ($lignes as $ligne) {
            $ligne = trim($ligne); // Supprime les espaces en début et fin de ligne
            if (!empty($ligne)) {
                $texteFormate .= "<p>" . $ligne . "</p>";
            }
        }
    
        return $texteFormate;
    }
    public static function text_to_html_class($text) {
        // Remplace les espaces par des tirets
        $text = str_replace(' ', '-', $text);
        
        // Supprime les caractères spéciaux
        $text = preg_replace('/[^A-Za-z0-9\-]/', '', $text);
        
        // Convertit les caractères en minuscules
        $text = strtolower($text);
        
        // Remplace les tirets consécutifs par un seul
        $text = preg_replace('/-+/', '-', $text);
        
        return $text;
    }
}
$cityKomi = new CityKomiPlugin();

