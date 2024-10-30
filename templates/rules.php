<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
<?php if ( $domain ) : ?>RewriteCond %{HTTP_HOST} <?= $domain; ?>$ [NC]<?php endif; ?>
<?php foreach( $dependencies as $dependency ) : ?>
RewriteRule ^<?= $dependency['url']; ?>(.*) <?= $dependency['dir']; ?>$1 [L]
<?php endforeach; ?>
</IfModule>