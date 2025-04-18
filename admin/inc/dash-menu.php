<?php
	$arr = array(
    "statistics" => array(
        "title" => "لوحة القيادة",
        "icon" => "chart-area",
        "link" => "statistics",
        "childs" => false,
    ),
    
    "users" => array(
        "title" => "الأعضاء",
        "icon" => "users",
        "link" => "#",
        "childs" => array(
            "members" => array("title" => "الأعضاء","icon" => "","link" => "users"),
			"addUser" => array("title" => "أضف عضو","icon" => "","link" => "users?section=add"),
			"users_settings" => array("title" => "إعدادات","icon" => "","link" => "users?section=settings"),
			"group-alert" => array("title" => "تنبيه جماعي","icon" => "","link" => "users?section=group-alert"),
		),
    ),
    
    "external_links" => array(
        "title" => "روابط خارجية",
        "icon" => "external-link-alt",
        "link" => "external_links",
        "childs" => false,
    ),

    "taxonomies" => array(
        "title" => "تصنيفات",
        "icon" => "sitemap",
        "link" => "taxonomies",
        "childs" => false,
    ),
    
    "points" => array(
        "title" => "إعدادات حصص النشر",
        "icon" => "trophy",
        "link" => "points",
        "childs" => false,
    ),
    
    "files" => array(
        "title" => "مركز الرفع",
        "icon" => "image",
        "link" => "gallery",
        "childs" => array(
			"files" => array("title" => "معرض الصور","icon" => "","link" => "files"),
			"categories" => array("title" => "الأقسام","icon" => "","link" => "files?section=categories"),
			"files_add_cat" => array("title" => "أضف أقسام","icon" => "","link" => "files?action=add"),
        ),
    ),

    "posts" => array(
        "title" => "المشاركات",
        "icon" => "file",
        "link" => "posts",
        "childs" => false,
    ),

    "books" => array(
        "title" => "الكتب",
        "icon" => "book",
        "link" => "books",
        "childs" => false,
    ),

    "authors" => array(
        "title" => "المؤلفين",
        "icon" => "users",
        "link" => "authors",
        "childs" => false,
    ),

    "contents" => array(
        "title" => "جلب المحتوى",
        "icon" => "recycle",
        "link" => "contents",
        "childs" => array(
			"contents" => array("title" => "الروابط الجاهزة","icon" => "","link" => "contents"),
			"fetch" => array("title" => "معرض الجلب","icon" => "","link" => "contents?action=fetch&filter_fetch=1"),
			"add_contents" => array("title" => "أضف محتوى","icon" => "","link" => "contents?action=add"),
        ),
    ),

    "boots" => array(
        "title" => "بوتات",
        "icon" => "robot",
        "link" => "boots",
        "childs" => array(
			"boots" => array("title" => "البوتات","icon" => "","link" => "boots"),
			"add_boots" => array("title" => "أضف بوت","icon" => "","link" => "boots?action=add"),
			"boot_comments" => array("title" => "عرض تعليقات البوت","icon" => "","link" => "boots?action=comments"),
			"add_boot_comments" => array("title" => "أضف تعليقات للبوت","icon" => "","link" => "boots?action=add_comment"),
        ),
    ),
    
    "ads" => array(
        "title" => "الإعلانات",
        "icon" => "bullhorn",
        "link" => "ads",
        "childs" => false,
    ),  
    "categories" => array(
        "title" => "الأقسام",
        "icon" => "bars",
        "link" => "#",
        "childs" => array(
			"add_categories" => array("title" => "أضف قسم", "icon" => "","link" => "categories?section=add_categories&action=add"),
			"categories" => array("title" => "الأقسام", "icon" => "","link" => "categories"),
        ),
    ),
    "pages" => array(
        "title" => "الصفحات",
        "icon" => "file",
        "link" => "#",
        "childs" => array(
			"add_pages" => array("title" => "أضف صفحة", "icon" => "","link" => "pages?section=add_pages&action=add"),
			"pages" => array("title" => "الصفحات", "icon" => "","link" => "pages"),
        ),
    ),    

    "countries" => array(
        "title" => "الدول",
        "icon" => "file",
        "link" => "#",
        "childs" => array(
			"add_pages" => array("title" => "أضف دولة", "icon" => "","link" => "countries?action=add"),
			"pages" => array("title" => "الدول", "icon" => "","link" => "countries"),
        ),
    ),
	
	"social_accounts" => array(
        "title" => "مواقع التواصل الإجتماعي",
        "icon" => "share",
        "link" => "social_accounts",
        "childs" => false,
    ),    
	
	"contact" => array(
        "title" => "إتصل بنا",
        "icon" => "envelope",
        "link" => "contact",
        "childs" => false,
    ),

	"complains" => array(
        "title" => "الشكايات",
        "icon" => "envelope",
        "link" => "complains",
        "childs" => false,
    ),
    
    "badges" => array(
        "title" => "الأوسمة",
        "icon" => "id-badge",
        "link" => "badges",
        "childs" => false,
    ),
    "general_settings" => array(
        "title" => "إعدادات عامة",
        "icon" => "cog",
        "link" => "general_settings",
        "childs" => false,
    ),
     "advanced_settings" => array(
        "title" => "إعدادات متقدمة",
        "icon" => "cogs",
        "link" => "#",
        "childs" => array(
			"seo" => array("title" => "إعدادات سيو","icon" => "","link" => "advanced_settings?section=seo"),
			"watermark" => array("title" => "العلامة المائية","icon" => "","link" => "advanced_settings?section=watermark"),
			"languages" => array("title" => "إعدادات اللغة","icon" => "","link" => "advanced_settings?section=languages"),
			"roles" => array("title" => "إعدادات الرتب","icon" => "","link" => "advanced_settings?section=roles"),
			"information-box" => array("title" => "صندوق المعلومات","icon" => "","link" => "advanced_settings?section=information-box"),
			"homepage" => array("title" => "إعدادات بلوكات","icon" => "","link" => "advanced_settings?section=homepage"),
        ),
    ),   
    
);			
?>