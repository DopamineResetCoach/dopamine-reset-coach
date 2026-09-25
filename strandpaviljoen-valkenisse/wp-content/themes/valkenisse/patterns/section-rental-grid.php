<?php
/**
 * Title: Strandverhuur – fotogrid (stoelen, ligbedden, parasols, windschermen)
 * Slug: valkenisse/section-rental-grid
 * Categories: valkenisse-secties
 * Keywords: verhuur, strandstoel, ligbed, parasol, windscherm
 * Viewport Width: 1400
 */
$vk_items = array(
	array( 'strandstoelen', __( 'Strandstoelen', 'valkenisse' ), __( 'Strandstoelen op het strand van Valkenisse', 'valkenisse' ) ),
	array( 'ligbedden', __( 'Ligbedden', 'valkenisse' ), __( 'Ligbedden op het strand bij het paviljoen', 'valkenisse' ) ),
	array( 'parasols', __( 'Parasols', 'valkenisse' ), __( 'Parasols op het strand', 'valkenisse' ) ),
	array( 'windschermen', __( 'Windschermen', 'valkenisse' ), __( 'Windschermen op het strand', 'valkenisse' ) ),
);
?>
<!-- wp:group {"align":"wide","className":"vk-rental-grid","layout":{"type":"grid","minimumColumnWidth":"14rem"}} -->
<div class="wp-block-group alignwide vk-rental-grid">
<?php foreach ( $vk_items as $vk_item ) : ?>
<!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"medium_large","className":"vk-rental-grid__item vk-reveal"} -->
<figure class="wp-block-image size-medium_large vk-rental-grid__item vk-reveal"><img src="<?php echo valkenisse_photo( $vk_item[0] ); ?>" alt="<?php echo esc_attr( $vk_item[2] ); ?>" style="aspect-ratio:1;object-fit:cover"/><figcaption class="wp-element-caption"><?php echo esc_html( $vk_item[1] ); ?></figcaption></figure>
<!-- /wp:image -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
