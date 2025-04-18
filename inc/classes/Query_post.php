<?php

/** 
 * Query_topics 
 * Manage select queries for posts
 *
 * @prop array $args
 */

class Query_post
{

	private $args = [];
	private $count_results = 0;
	private $posts;
	private $infos;
	public $do_query_count = false;
	function __construct($args)
	{
		if ($args) {
			$this->get_post_args($args);
		}
	}

	private function get_post_args($args)
	{
		$this->args['post_id'] = $args['post_id'] ?? null;
		$this->args['post_title'] = $args['post_title'] ?? '';
		$this->args['post_title_like'] = $args['post_title_like'] ?? '';
		$this->args['post_status'] = $args['post_status'] ?? null;
		$this->args['post_status__not'] = $args['post_status__not'] ?? null;
		$this->args['post_type'] = $args['post_type'] ?? null;
		$this->args['post_type__not'] = $args['post_type__not'] ?? null;
		$this->args['post_in'] = $args['post_in'] ?? null;
		$this->args['post_lang'] = $args['post_lang'] ?? null;
		$this->args['post_author'] = $args['post_author'] ?? null;
		$this->args['in_slide'] = $args['in_slide'] ?? null;
		$this->args['reviewed'] = $args['reviewed'] ?? null;
		$this->args['share_authority'] = $args['share_authority'] ?? null;
		$this->args['in_special'] = $args['in_special'] ?? null;
		$this->args['info_id'] = $args['info_id'] ?? null;
		$this->args['limit'] = $args['limit'] ?? null;
		$this->args['post_meta'] = $args['post_meta'] ?? null;
		$this->args['post_category'] = $args['post_category'] ?? null;
		$this->args['order'] = $args['order'] ?? null;
		$this->args['fetch'] = $args['fetch'] ?? 'false';
	}

	public function set_post_info_data($args)
	{
		$this->args['info_id'] = $args['info_id'] ?? null;
		$this->args['post_author'] = $args['post_author'] ?? '';
		$this->args['post_key'] = $args['post_key'] ?? '';
		$this->args['post_date_gmt'] = $args['post_date_gmt'] ?? null;
		$this->args['post_status'] = $args['post_status'] ?? null;
		$this->args['post_type'] = $args['post_type'] ?? null;
		$this->args['number_fetch'] = $args['number_fetch'] ?? 10;
		$this->args['post_in'] = $args['post_in'] ?? null;
		$this->args['post_lang'] = $args['post_lang'] ?? null;
		$this->args['post_category'] = $args['post_category'] ?? null;
		$this->args['limit'] = $args['limit'] ?? null;
		$this->args['order'] = $args['order'] ?? null;
	}

	public function get_posts()
	{
		$this->get_results();
		return $this->posts;
	}

	private function get_results_info()
	{

		global $dsql;

		$query = $dsql->dsql()->table('post_info')->field('post_info.*');
		$info_id = $this->args["info_id"];
		$post_author = $this->args["post_author"];
		$post_key = $this->args['post_key'];
		$post_status = $this->args["post_status"];
		$post_type = $this->args["post_type"];
		$number_fetch = $this->args["number_fetch"];
		$post_in = $this->args["post_in"];
		$post_lang = $this->args["post_lang"];
		$limit = $this->args["limit"] ?? RESULT_PER_PAGE;
		$order = $this->args["order"];
		$post_category = $this->args["post_category"];

		if ($info_id) {
			$query->where('post_info.id', $info_id);
		}

		if ($post_key) {
			$query->where('post_info.post_key', '=', $post_key);
		}

		if ($post_status) {
			$query->where('post_info.post_status', $post_status);
		}

		if ($post_type) {
			$query->where('post_info.post_type', $post_type);
		}

		if ($post_in) {
			$query->where('post_info.post_in', $post_in);
		}

		if ($post_lang) {
			$query->where('post_info.post_lang', $post_lang);
		}

		if ($post_author) {
			$query->where('post_info.post_author', $post_author);
		}

		if ($post_category) {
			$query->join('categories.id', 'post_info.post_category', 'inner')->where('categories.id', $post_category);
		}

		if (is_array($order)) {
			$query->order($order[0], $order[1]);
		} elseif ($order) {
			$query->order($query->expr($order));
		}

		// passing number of all rows before limiting results
		if ($this->do_query_count) {
			$query_count = $query->field('count(*)')->getRow();
			if ($query_count) {
				$this->count_results = count_rows($query_count);
			}

			// @ read more here (https://dsql.readthedocs.io/en/develop/queries.html?highlight=limit#Query::reset)
			$query->reset('getRow')->reset('field');
		}

		// dd($query);

		$query->limit(paged('end', $limit), paged('start', $limit));

		$results = $query->get();

		$infos = [];

		if (count($results) > 0) {
			foreach ($results as $info) {

				$get_cache = get_cache($info["id"], 'infos');

				if ($get_cache) {
					$infos[] = $get_cache;
				} else {
					$get_info = $dsql->dsql()->table('post_info')->where('id', $info["id"])->limit(1)->getRow();
					add_cache($info["id"], 'infos', $get_info);
					$infos[] = $get_info;
				}
			}

			$this->infos = $infos;
		}

		$this->get_post_author();
	}

	public function get_info()
	{
		$this->get_results_info();
		return $this->infos ?? [];
	}

	public function get_post()
	{
		$this->get_results();
		return $this->posts[0] ?? [];
	}

	public function count_results()
	{
		if (false === $this->do_query_count || empty($this->posts)) {
			return false;
		}
		return $this->count_results;
	}

	private function get_results()
	{

		global $dsql;

		$query = $dsql->dsql()->table('posts');

		$post_id = $this->args["post_id"] ?? 0;
		$post_title = $this->args['post_title'] ?? '';
		$post_title_like = $this->args['post_title_like'] ?? '';
		$post_status = $this->args["post_status"];
		$post_status__not = $this->args["post_status__not"] ?? '';
		$post_type = $this->args["post_type"];
		$post_type__not = $this->args["post_type__not"] ?? '';
		$post_in = $this->args["post_in"];
		$post_lang = $this->args["post_lang"];
		$post_author = $this->args["post_author"];
		$in_slide = $this->args["in_slide"] ?? '';
		$in_special = $this->args["in_special"] ?? '';
		$reviewed = $this->args["reviewed"] ?? '';
		$share_authority = $this->args["share_authority"] ?? '';
		$info_id = $this->args["info_id"] ?? null;
		$limit = $this->args["limit"] ?? RESULT_PER_PAGE;
		$order = $this->args["order"];
		$fetch = $this->args["fetch"];

		$post_meta = $this->args["post_meta"] ?? null;
		$post_category = $this->args["post_category"];

		if ($post_id) {
			$query->where('posts.id', $post_id);
		}

		if ($info_id) {
			$query->where('posts.info_id', $info_id);
		}

		if ($post_title_like) {
			$query->where('posts.post_title', 'like', $post_title_like);
		}

		if ($post_title) {
			$query->where('posts.post_title', 'like', '%' . $post_title . '%');
		}

		if ($fetch == 'true') {
			$query->where('posts.info_id', 'NOT', 'NULL');
		}

		if ($post_status) {
			$query->where('posts.post_status', $post_status);
		}

		if ($post_status__not) {
			$query->where('post_status', 'NOT IN', $post_status__not);
		}

		if ($post_type) {
			$query->where('posts.post_type', $post_type);
		}

		if ($post_type__not) {
			$query->where('post_type', 'NOT IN', $post_type__not);
		}

		if ($post_in) {
			$query->where('posts.post_in', $post_in);
		}

		if ($post_lang) {
			$query->where('posts.post_lang', $post_lang);
		}

		if ($post_author) {
			$query->where('posts.post_author', $post_author);
		}

		if ($in_slide) {
			$query->where('posts.in_slide', $in_slide);
		}

		if ($in_special) {
			$query->where('posts.in_special', $in_special);
		}

		if ($post_category) {
			$query->join('post_category.post_id', 'posts.id')
				->where('post_category.post_category', $post_category);
		}

		// dd($query);

		if (is_array($post_meta)) {
			$expr = 'post_meta.post_id = posts.id';
			$where = false;
			if (isset($post_meta['post_audio'])) {
				
				if ($post_meta['post_audio'] == 'listen') {
					$expr .= " AND post_meta.meta_key = 'audios_ids'";
					$where = "posts.post_type = 'book' AND post_meta.id IS NOT NULL";
					// ->where($query->expr("posts.post_type = 'book' AND post_meta.id IS NOT NULL"));
				} elseif ($post_meta['post_audio'] == 'unlisten') {
					$expr .= " AND post_meta.meta_key = 'audios_ids'";
					$where = "posts.post_type = 'book' AND post_meta.id IS NULL";
					// ->where($query->expr("posts.post_type = 'book' AND post_meta.id IS NULL"));
				}
			}

			$query->join('post_meta', $query->expr($expr));
			if($where !== false) {
				$query->where($query->expr($where));
			}
			
			if(!isset($post_meta['post_audio']) && !isset($post_meta['order_meta']) && is_array($post_meta) && count($post_meta) > 0) {
				$meta_where_clause = '';
				$l = 0;
				foreach ($post_meta as $meta) {
					if (!empty($meta[2])) {
						if ($l > 0) {
							$meta_where_clause .= "AND";
						}
						$meta_where_clause .= "`post_meta`.`meta_key` = '" . $meta[0] . "' and `post_meta`.`meta_value` = '" . $meta[2] . "'";
						$l++;
					}
				}
				$query->where($query->expr($meta_where_clause));
			}
			if (isset($post_meta['order_meta'])) {
				switch ($post_meta['order_meta']) {
					case 'more_downloads':
						$query->where('post_meta.meta_key', 'book_downloads')
							->order($query->expr('CAST(post_meta.meta_value AS UNSIGNED) DESC'));
						break;
	
					case 'more_previews':
						$query->where('post_meta.meta_key', 'book_preview')
							->order($query->expr('CAST(JSON_EXTRACT(post_meta.meta_value, "$.preview") AS UNSIGNED) DESC'));
						break;
	
					case 'more_listens':
						$query->where('post_meta.meta_key', 'book_listen')
							->order($query->expr('CAST(JSON_EXTRACT(post_meta.meta_value, "$.listen") AS UNSIGNED) DESC'));
						break;
				}
			} else {
				if (is_array($order)) {
					$query->order($order[0], $order[1]);
				} elseif ($order) {
					$query->order($query->expr($order));
				}
			}

		}

		// passing number of all rows before limiting results
		if ($this->do_query_count) {
			$query_count = $query->field('count(*)')->getRow();
			if ($query_count) {
				$this->count_results = count_rows($query_count);
			}

			$query->reset('getRow')->reset('field');
		}

		

		if (isset($post_meta['order_meta'])) {
			$query->field('distinct(posts.id), posts.post_views, posts.post_date_gmt, post_meta.meta_value');
		} else {
			$query->field('distinct(posts.id), posts.post_views, posts.post_date_gmt');
		}

		if ($limit == 'all') {
			goto no_limit;
		}
		$query->limit(paged('end', $limit), paged('start', $limit));
		no_limit:


		$results = $query->get();

		$posts = [];


		if (count($results) > 0) {
			foreach ($results as $post) {

				$get_cache = get_cache($post["id"], 'posts');

				if ($get_cache) {
					$posts[] = $get_cache;
				} else {
					$get_post = $dsql->dsql()->table('posts')->where('id', $post["id"])->limit(1)->getRow();
					add_cache($post["id"], 'posts', $get_post);
					$posts[] = $get_post;
				}
			}

			$this->posts = $posts;
		}

		$this->meta();
		$this->get_post_author();
	}

	/**
	 * Return all posts metadata
	 */
	public function meta()
	{

		if (empty($this->posts)) {
			return false;
		}

		global $dsql;
		$posts = array_column($this->posts, 'id');
		$query = $dsql->dsql()->table('post_meta')->where('post_id', $posts)->get();
		$post_meta = [];

		if ($query) {
			foreach ($query as $meta) {
				$post_meta[$meta["post_id"]][$meta["meta_key"]] = $meta["meta_value"];
				// Store all returned posts meta in @global $cache_obj
				add_cache($meta["post_id"], 'post_meta', $post_meta[$meta["post_id"]]);
			}
		}

		return $post_meta;
	}

	public function get_post_author()
	{

		if (empty($this->posts)) {
			return false;
		}

		global $dsql;

		$authors = array_column($this->posts, 'post_author');
		$query = $dsql->dsql()->table('users')->where('id', $authors)->get();

		$authors_info = [];

		if ($query) {
			foreach ($query as $author) {
				$authors_info[$author["id"]] = $author;
				add_cache($author["id"], 'user_meta', $author);
			}
		}
		// Store all returned posts meta in @global $cache_obj

		return $authors_info;
	}

	/**
	 * get post_categories
	 * return post categories id
	 *
	 * @return mixed
	 */
	public function get_post_categories()
	{
		$info_id = isset($this->args['info_id']) ? $this->args['info_id'] : 0;
		$post_id = $this->args['post_id'] ?? $info_id;
		$table = $info_id && $info_id != 0 ? 'post_info' : 'post_category';
		$id_column = $info_id && $info_id != 0 ? 'id' : 'post_id';

		if (empty($post_id)) {
			return false;
		}

		global $dsql;

		$get_categories = $dsql->dsql()->table($table)->where($id_column, $post_id)->field('post_category')->get();

		if ($get_categories) {
			return array_column($get_categories, 'post_category');
		}

		return false;
	}

	/**
	 * Get slices for post
	 */
	public function get_post_slices($post_id = null, $order_by = "DESC")
	{
		if (empty($post_id)) {
			$post_id = $this->args['post_id'];
		}
		if (empty($post_id)) {
			return false;
		}
		global $dsql;
		$get_slices = $dsql->dsql()->table('post_slices')->where('post_id', $post_id)->order('id', $order_by)->get();
		return $get_slices;
	}
}
