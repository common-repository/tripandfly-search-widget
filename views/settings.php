<?php
/**
 * @var TripandflySearchWidget $this
 */
?>

<div class="wrap">
	<h2><?php _e( $this->plugin->displayName, 'tripandfly-search-widget' ) ?> &raquo; <?php esc_html_e( 'Settings', 'tripandfly-search-widget' ); ?></h2>

	<?php
	if ( isset( $this->message ) ) {
		?>
		<div class="updated fade"><p><?php echo esc_html( $this->message ); ?></p></div>
		<?php
	}
	if ( isset( $this->errorMessage ) ) {
		?>
		<div class="error fade"><p><?php echo esc_html( $this->errorMessage ); ?></p></div>
		<?php
	}
	?>
  <div class="inside">
    <p><?php _e( 'Install our WordPress travel-plugin to your website and help your visitors to find the cheapest flights, trains, buses and multimodal offers.', 'tripandfly-search-widget' ); ?></p>
    <p><?php _e( 'Paste the short code <b>[tripandfly_search]</b> in the body of the page to display the search form', 'tripandfly-search-widget' ); ?></p>
  </div>

  <form action="options-general.php?page=<?php echo esc_html( $this->plugin->name ); ?>" method="post">
    <table class="form-table" role="presentation">
      <tr>
        <th scope="row">
          <label for="tripandfly_search_partner_id"><?php esc_html_e( 'Partner ID', 'tripandfly-search-widget' ); ?></label>
        </th>
        <td>
          <input
            type="text"
            name="tripandfly_search_partner_id"
            id="tripandfly_search_partner_id"
              <?php echo ( ! current_user_can( 'unfiltered_html' ) ) ? ' disabled="disabled" ' : ''; ?>
            value="<?php echo esc_html( $this->settings['tripandfly_search_partner_id'] ); ?>"
          >
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label><?php esc_html_e( 'Language', 'tripandfly-search-widget' ); ?></label>
        </th>
        <td>
          <select name="tripandfly_search_lang" id="tripandfly_search_lang">
          <?php foreach ($this->languages as $locale => $language ) { ?>
            <option
              id="tripandfly_search_lang_<?php echo esc_html( $locale ) ?>"
              value="<?php echo esc_html( $locale ) ?>"
                <?php selected( true, ( !$this->settings['tripandfly_search_lang'] || $locale === $this->settings['tripandfly_search_lang']) );?>
            >
                <?php esc_html_e( $language, 'tripandfly-search-widget' ); ?>
            </option>
          <?php } ?>
          </select>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="tripandfly_search_partner_id"><?php esc_html_e( 'Enable debug mode', 'tripandfly-search-widget' ); ?></label>
        </th>
        <td>
          <input
            type="checkbox"
            name="tripandfly_search_debug_mode"
            id="tripandfly_search_debug_mode"
            <?php echo ( ! current_user_can( 'unfiltered_html' ) ) ? ' disabled="disabled" ' : ''; ?>
            <?php checked( true, ( $this->settings['tripandfly_search_debug_mode'] === 'Y' ) ); ?>
            value="Y"
          >
        </td>
      </tr>
    </table>
    <?php if ( current_user_can( 'unfiltered_html' ) ) : ?>
        <?php wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); ?>
      <p class="submit">
        <input name="submit" type="submit" name="Submit" class="button button-primary" value="<?php esc_attr_e( 'Save', 'tripandfly-search-widget' ); ?>" />
      </p>
    <?php endif; ?>
  </form>
</div>
