<?php
include 'init.php';


$action = isset($_POST['action']) ? $_POST['action'] : 'default';
$getAction = isset($_GET['action']) ? $_GET['action'] : 'default';

if (!function_exists("delete_file_ajax")) {
	/**
	 * delete_file_ajax()
	 */
	function delete_file_ajax($id = null)
	{
		$response = ["success" => false];

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		global $dsql;
		$file_id = $id ?? "";
		$get_file = $dsql->dsql()->table('files')->where('id', $file_id)->limit(1)->getRow();
		if (!$get_file) {
			return false;
		}

		// $file = $get_file;
		$delete = new Delete($file_id);
		if ($delete->delete_files()) {
			$response["success"]  = true;
		} else {
			$response['msg'] = _t('المعذرة ليس لك الصلاحيات لحدف الملف');
		}

		echo json_encode($response);
	}
}

// Function to retrieve original URL from the short code
function getOriginalUrl($shortCode) {
  global $dsql;

  $originalUrl = $dsql->dsql()->table('short_urls')->field('original_url')->where('short_code', $shortCode)->getRow();

  return $originalUrl ? $originalUrl['original_url'] : null;
}

if($action == 'createshortcut') {

  // Function to create a short URL
  function createShortUrl($url) {
    global $dsql;

    if($dsql->dsql()->table('short_urls')->where('original_url', $url)->getRow()) {
      $dsql->dsql()->table('short_urls')->where('original_url', $url)->delete();
    }

    // Generate a unique short code
    $shortCode = generateShortCode();

    // Store the original URL and short code in the database
    $insert = $dsql->dsql()->table('short_urls')->set(['original_url' => $url, 'short_code' => $shortCode])->insert();
    if($insert) {
      // Return the shortened link
      return $shortCode;
    }
    return false;
  }

  // Function to generate a unique short code
  function generateShortCode($length = 6) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $shortCode = '';
      
      // Generate a random short code
      for ($i = 0; $i < $length; $i++) {
          $shortCode .= $characters[rand(0, $charactersLength - 1)];
      }

      return 'lns-sh/' . $shortCode;
  }

  $originalUrl = SITEURL . $_POST["originalUrl"];
  
  $shortCode = createShortUrl($originalUrl);

  $data = [];

  if($shortCode) {
    $newUrl = getOriginalUrl($shortCode);
    $data['original'] = getOriginalUrl($shortCode);
    $data['short'] = SITEURL . "/" . $shortCode;
  }

  echo json_encode($data);

} elseif($action == 'deleteaudio') {
  $id = isset($_POST['id']) ? $_POST['id'] : 0;
  if($id == 0) {
    die("Page not found");
    exit;
  }
  delete_file_ajax($id);

} elseif ($action == 'updatereview') {
  $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
  $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
  if ($user_id == 0 || $post_id == 0) {
      return false; // Invalid input
  }

  // Fetch the existing book_preview meta value
  $book_preview = $dsql->dsql()
      ->table('post_meta')
      ->where('post_id', $post_id)
      ->where('meta_key', 'book_preview')
      ->field('meta_value')
      ->limit(1)
      ->getRow();

  $preview = 0;
  
  if (!$book_preview || empty($book_preview)) {
      // Insert new record if no book_preview exists
      $preview++;
      $book_preview_info = json_encode([
          "user_ids" => [$user_id], // Track user_ids who viewed
          "preview" => $preview
      ]);

      $insert = $dsql->dsql()->table('post_meta')->set([
          "meta_key" => "book_preview",
          "meta_value" => $book_preview_info,
          "post_id" => $post_id
      ])->insert();

      if($insert) {
        echo $preview;
        exit;
      }
  } else {
      // Deserialize the existing value
      $book_preview_info = json_decode($book_preview['meta_value'], true);

      if (!isset($book_preview_info['user_ids']) || !in_array($user_id, $book_preview_info['user_ids'])) {
          // Only increment preview if the user has not viewed
          $book_preview_info['user_ids'][] = $user_id;
          $book_preview_info['preview']++;
      }

      // Update the existing record
      $update = $dsql->dsql()->table('post_meta')->set([
          "meta_value" => serialize($book_preview_info)
      ])->where('post_id', $post_id)
      ->where('meta_key', 'book_preview')
      ->update();
      if($update) {
        echo $book_preview_info['preview'];
        exit;
      }
  }

  return true;
  
} elseif ($action == 'updatelisten') {

  $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
  $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;

  if ($user_id == 0 || $post_id == 0) {
      return false; // Invalid input
  }

  // Fetch the existing book_preview meta value
  $book_listen = $dsql->dsql()
      ->table('post_meta')
      ->where('post_id', $post_id)
      ->where('meta_key', 'book_listen')
      ->field('meta_value')
      ->limit(1)
      ->getRow();

  $listen = 0;

  if (!$book_listen || empty($book_listen)) {
      // Insert new record if no book_listen exists
      $listen++;
      $book_listen_info = json_encode([
        "user_ids" => [$user_id], // Track user_ids who viewed
        "listen" => $listen
      ]);

      $insert = $dsql->dsql()->table('post_meta')->set([
          "meta_key" => "book_listen",
          "meta_value" => $book_listen_info,
          "post_id" => $post_id
      ])->insert();

      if($insert) {
        echo $listen;
        exit;
      }
  } else {
      // Deserialize the existing value
      $book_listen_info = json_decode($book_listen['meta_value'], true);

      if (!isset($book_listen_info['user_ids']) || !in_array($user_id, $book_listen_info['user_ids'])) {
          // Only increment listen if the user has not viewed
          $book_listen_info['user_ids'][] = $user_id;
          $book_listen_info['listen']++;
      }

      // Update the existing record
      $update = $dsql->dsql()->table('post_meta')->set([
          "meta_value" => json_encode($book_listen_info)
      ])->where('post_id', $post_id)
      ->where('meta_key', 'book_listen')
      ->update();
      if($update) {
        echo $book_listen_info['listen'];
        exit;
      }
  }

  return true;

} elseif($action == 'unionauthornames') {
  if($_SERVER['REQUEST_METHOD'] == "POST") {
    $ids = $_POST['id'] ?? [];
    $name = $_POST['book_author'] ?? "";
    if(count($ids) <= 0 || empty($name)) {
      echo 'false';
      exit;
    }
    $name = intval($name) > 0 ? get_authors($name)['name'] : $name;
    $ids_str = implode(",", $ids);
    $success = 0;
    $delete = $dsql->dsql()->table('authors')->where($dsql->expr("id IN ($ids_str)"))->delete();
    if($delete) {
      $insert = $dsql->dsql()->table('authors')->set(['name' => $name])->insert();
      if($insert) {
        $id = $dsql->lastInsertId();
        if($id) {
          $update_id = $dsql->dsql()->table('post_meta')->set(["meta_key" => "book_author_id", "meta_value" => $id])->where($dsql->expr("meta_key = 'book_author_id' AND meta_value IN ($ids_str)"))->update();
          if($update_id) {
            $posts = $dsql->dsql()->table('post_meta')->where($dsql->expr("meta_key = 'book_author_id' AND meta_value = $id"))->field('post_id')->get();
            if(is_array($posts) && count($posts) > 0) {
              foreach($posts as $post) {
                $post_id = $post['post_id'];
                $update = $dsql->dsql()->table('post_meta')->set(['meta_key' => 'book_author', 'meta_value' => $name])->where($dsql->expr("meta_key = 'book_author' AND post_id = $post_id"))->update();
                if($update) {
                  $success++;
                }
              }
            }
            if($success == count($posts)) {
              echo "ok";
              exit;
            }
          }
        }
      }
    }
  }
} elseif($action == 'authorchangestat') {
  if($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = $_POST['id'] ?? 0;
    $stat = $_POST['stat'] ?? "";
    if($id == 0 || empty($stat)) {
      echo "false";
      exit;
    }
    $stat = $stat == 'en' ? 1 : 0;
    $update = $dsql->dsql()->table('authors')->set(["author_stat" => $stat])->where("id", $id)->update();
		if ($update) {
			echo "ok";
      exit;
		} else {
			echo "false";
      exit;
		}
  }
} elseif($action == 'authorsearchname') {
  if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['value'])) {
    $search_term = $_POST['value'];

    global $dsql;

		$query = $dsql->dsql()->table('authors');
		if (!empty($search_term)) {
			$query = $query->where($query->expr("name LIKE '%". $search_term ."%'"));
		} else {
      $query = $query->order('id', 'desc')->limit(10);
    }
		$filtered_authors = $query->get();
    echo json_encode($filtered_authors); // Reset array keys
    exit;
  }
} elseif($action == 'translatorsearchname') {
  if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['value'])) {
    $search_term = $_POST['value'];

    global $dsql;

		$query = $dsql->dsql()->table('translators');
		if (!empty($search_term)) {
			$query = $query->where($query->expr("name LIKE '%". $search_term ."%'"));
		} else {
      $query = $query->order('id', 'desc')->limit(10);
    }
		$filtered_translators = $query->get();
    echo json_encode($filtered_translators); // Reset array keys
    exit;
  }
}


if($getAction == 'redirect') {
  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $shortCode = trim($path, '/');
  $originalUrl = getOriginalUrl($shortCode);
  if(!is_null($originalUrl)) {
    header("Location: $originalUrl");
  } else {
    die("Page not found");
  }
  exit;
}

if(isset($_GET['page'])) {
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $limit = 10; // Number of users per page
  $offset = ($page - 1) * $limit;
  
  $users = $dsql->dsql()
      ->expr("SELECT users.*, rating_sys.id as r_id FROM users INNER JOIN rating_sys ON users.id = rating_sys.user_id WHERE rating_sys.post_id = $post_id LIMIT $limit OFFSET $offset;")
      ->get();
  
  echo json_encode($users);
} 
