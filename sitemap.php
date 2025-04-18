<?php
require_once 'init.php';
header("content-type:text/xml");
header('Content-Disposition: attachment; filename="sitemap.xml"');
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
$paged = $_GET['paged'] ?? 1;
$get_posts = $dsql->dsql()->table('posts')->where('post_status','publish')->field('id,post_title,post_url_title,post_date_gmt,post_type')->limit(paged('end',40000),paged('start',40000))->get();
$get_pages = get_pages();
$get_taxonomies = get_all_taxonomies();
/* posts */
if(is_array($get_posts)):
foreach( $get_posts as $post ):
?>	
   <url>

      <loc><?php echo get_post_link($post); ?></loc>

      <lastmod><?php esc_html(gmdate('Y-m-d\TH:i:s+00:00', strtotime($post["post_date_gmt"]))); ?></lastmod>

      <priority>1</priority>

   </url>
<?php
endforeach;
endif;
/* taxonomies */
if(is_array($get_taxonomies) && $paged == 1):
foreach( $get_taxonomies as $taxo ):
	?>
   <url>

      <loc><?php esc_html(siteurl()."/posts/".$taxo['taxo_type']); ?></loc>

      <priority>0.8</priority>

   </url>
	<?php
endforeach;
endif;
/* pages */
if(is_array($get_pages) && $paged == 1):
foreach( $get_pages as $page ):
$page_link = siteurl()."/page/".$page["id"]."/".$page["page_title"];
?>
   <url>

      <loc><?php esc_html($page_link); ?></loc>

      <priority>0.5</priority>

   </url>

<?php
endforeach;
endif;
?>
</urlset> 
