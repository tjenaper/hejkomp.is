<?php

	date_default_timezone_set('Europe/Stockholm');

	class Grav {

		static public function get_saved_items() {

			$data = Content::get_all();

			$blog_directories = glob(BLOG_PATH . '/*' , GLOB_ONLYDIR);

			foreach($data as $key => $value) {

				$folder_name = $value->folder_name;
				$post_file = '../user/pages/blog/'.$folder_name.'/post.md';
				$directory_path = BLOG_PATH.'/'.$folder_name;

				if(in_array($directory_path, $blog_directories)) {
					$key = array_search($directory_path, $blog_directories);
					unset($blog_directories[$key]);
				}

				if (!file_exists('../user/pages/blog/'.$folder_name)) {
					mkdir('../user/pages/blog/'.$folder_name, 0777, true);

					$tags = !empty($value->tags) ? implode(',', $value->tags) : '';

					$content = "---\r\n";
					$content .= "title:  ".$value->name."\r\n";
					$content .= "slug:  ".$value->slug."\r\n";
					$content .= "source:  ".$value->url."\r\n";
					$content .= "date:  ".date('Y-m-d H:i', $value->timestamp)."\r\n";
					$content .= "taxonomy:"."\r\n  tag: [".$tags."]\r\n";

					$img_data = [];

					if($value->image) {

						$img_data = getimagesize($value->image);

						if($img_data[0] > 320 && $img_data[1] > 320) {
						
							$image = Content::curl_get_contents($value->image);

							$fp = fopen('../user/pages/blog/'.$folder_name.'/image.jpg', 'w');
							fwrite($fp, $image);
							fclose($fp);

							$content .= "image:  image.jpg\r\n";

						}
						
					}

					$content .= "---";

					$img_data = [];

					$fp = fopen($post_file,'wb');
					fwrite($fp,$content);
					fclose($fp);
					
				} else {

					// hämta ut innehållet
					$fileData = self::read_contents_of_file($post_file);
					// vi utgår från att inget måste uppdateras
					$update_content = false;

					$image_to_content = '';

					if(isset($fileData['image'])) {
						$image_to_content = "image:  image.jpg\r\n";
					} else {

						if($value->image) {

							$img_data = getimagesize($value->image);

							if($img_data[0] > 320 && $img_data[1] > 320) {
							
								$image = Content::curl_get_contents($value->image);

								$fp = fopen('../user/pages/blog/'.$folder_name.'/image.jpg', 'w');
								fwrite($fp, $image);
								fclose($fp);

								$image_to_content = "image:  image.jpg\r\n";
								$update_content = true;

							}
							
						}

					}

					// kolla om taggarna är desamma
					$pocket_tags = is_array($value->tags) ? $value->tags : [];
					$file_tags = is_array($fileData['tagArray']) ? $fileData['tagArray'] : [];

					if(array_diff($pocket_tags, $file_tags)) {

						// om inte, uppdatera taggarna
						$file_tags = $pocket_tags;
						$update_content = true;

					}

					$tags = !empty($file_tags) ? implode(',', $file_tags) : '';

					if($update_content) {

						// samla ihop innehållet
						$content = "---\r\n";
						$content .= "title:  ".$fileData['title']."\r\n";
						$content .= "slug:  ".$fileData['slug']."\r\n";
						$content .= "source:  ".$fileData['source']."\r\n";
						$content .= "date:  ".date('Y-m-d H:i', $fileData['timestamp'])."\r\n";
						$content .= "taxonomy:"."\r\n  tag: [".$tags."]\r\n";
						$content .= $image_to_content;

						$content .= "---";
					
						// spara i fil
						$fp = fopen($post_file,'wb');
						fwrite($fp,$content);
						fclose($fp);

					}
					
				}

			}

			foreach($blog_directories as $blog_directory) {

				self::delete_dir($blog_directory);

			}

		}

		static public function save_item($input) {

			$folder_name = $input->folder_name;
			$directory_path = BLOG_PATH.'/'.$folder_name;

			if (!file_exists('../user/pages/blog/'.$folder_name)) {
				mkdir('../user/pages/blog/'.$folder_name, 0777, true);

				$post_file = '../user/pages/blog/'.$folder_name.'/post.md';

				$tags = !empty($input->tags) ? implode(',', $input->tags) : '';

				$content = "---\r\n";
				$content .= "title:  ".$input->name."\r\n";
				$content .= "slug:  ".$input->slug."\r\n";
				$content .= "source:  ".$input->url."\r\n";
				$content .= "date:  ".date('Y-m-d H:i', $input->timestamp)."\r\n";
				$content .= "taxonomy:"."\r\n  tag: [".$tags."]\r\n";

				$img_data = [];

				if($input->image) {

					$image = Content::curl_get_contents($input->image);
					$imageUrl = '../user/pages/blog/'.$folder_name.'/image.jpg';

					$fp = fopen($imageUrl, 'w');
					fwrite($fp, $image);
					fclose($fp);

					$img_data = getimagesize($imageUrl);

					if($img_data[0] > 320 && $img_data[1] > 320) {
					
						$content .= "image:  image.jpg\r\n";

					}
					
				}

				$content .= "---";

				$img_data = [];

				$fp = fopen($post_file,'wb');
				fwrite($fp,$content);
				fclose($fp);
				
			} else {

				$post_file = '../user/pages/blog/'.$folder_name.'/post.md';
				if(!file_exists($post_file)) {
					$post_file = rtrim($post_file, ".md").'.sv.md';
				}

				// hämta ut innehållet
				$fileData = self::read_contents_of_file($post_file);
				// vi utgår från att inget måste uppdateras
				$update_content = false;

				$image_to_content = '';

				if(isset($fileData['image'])) {
					$image_to_content = "image:  image.jpg\r\n";
				} else {

					if($input->image) {

						$image = Content::curl_get_contents($input->image);
						$imageUrl = '../user/pages/blog/'.$folder_name.'/image.jpg';

						$fp = fopen($imageUrl, 'w');
						fwrite($fp, $image);
						fclose($fp);

						$img_data = getimagesize($imageUrl);

						if($img_data[0] > 320 && $img_data[1] > 320) {
						
							$image_to_content = "image:  image.jpg\r\n";
							$update_content = true;

						}
						
					}

				}

				// kolla om taggarna är desamma
				$fetched_tags = is_array($input->tags) ? $input->tags : [];

				$file_tags = is_array($fileData['tagArray']) ? $fileData['tagArray'] : [];

				$diff1 = array_diff($fetched_tags, $file_tags);
				$diff2 = array_diff($file_tags, $fetched_tags);

				$diff = array_merge($diff1, $diff2);

				if(count($diff) > 0) {

					// om inte, uppdatera taggarna
					$file_tags = $fetched_tags;
					$update_content = true;

				}

				$tags = !empty($file_tags) ? implode(',', $file_tags) : '';

				if($update_content) {

					$date = $fileData['date'];
					$date = ltrim($date, "'");
					$date = rtrim($date, "'");

					// samla ihop innehållet
					$content = "---\r\n";
					$content .= "title:  ".$fileData['title']."\r\n";
					$content .= "slug:  ".$fileData['slug']."\r\n";
					$content .= "source:  ".$fileData['source']."\r\n";
					$content .= "date:  ".$date."\r\n";
					$content .= "taxonomy:"."\r\n  tag: [".$tags."]\r\n";
					$content .= $image_to_content;

					$content .= "---";
					if($fileData['comment']) {
						$content .= $fileData['comment'];
					}
				
					// spara i fil
					$fp = fopen($post_file,'wb');
					fwrite($fp,$content);
					fclose($fp);

				}
				
			}

		}

		public static function publish_item($data) {
			
			if(empty($data['tag'])) { echo 'tag saknas, stänger av.'; die; }
			$date = !empty($data['date']) ? strtotime($data['date']) : time();

			$selectedPosts = [];
			$imgUrl = '';

			$di = new RecursiveDirectoryIterator('../user/pages/blog/');
			foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
				
				if($file->getFilename() == 'post.md' || $file->getFilename() == 'post.sv.md') {
					
					$postDataArray = self::read_contents_of_file($file->getRealPath());

					if(in_array($data['tag'], $postDataArray['tagArray'])) {
						array_push($selectedPosts, $postDataArray);
					}

				}

				if($file->getFilename() == 'image.jpg') {
					$imgUrl = str_replace('..', 'http://'.ROOT, $file->getPathname());
				} else {
					$imgUrl = '';
				}

				$postmd = '';
				
			}

			$lastWeeksPosts = [];

			$end_of_today = strtotime('Today, 23:59:59');
			$beginning_of_seven_days_ago = ($end_of_today+1)-(8*24*60*60);

			echo date('Y-m-d H:i:s',$beginning_of_seven_days_ago).' - '.date('Y-m-d H:i:s',$end_of_today).'<br /><br />';

			foreach($selectedPosts as $post) {
				if($post['timestamp'] <= $end_of_today && $post['timestamp'] >= $beginning_of_seven_days_ago) {
					array_push($lastWeeksPosts, $post);
				}
			}

			if(!empty($lastWeeksPosts)) {
				$selectedPost = self::get_random_post($lastWeeksPosts);
				$selectedPost['post_type'] = 'last_week';
			} elseif(!empty($selectedPosts)) {
				$selectedPost = self::get_random_post($selectedPosts);
				$selectedPost['post_type'] = 'all_time';
			} else {
				die('Hittar inga sparade poster med taggen '.$data['tag'].'.');
			}

			return $selectedPost;

		}
		
		private static function read_contents_of_file($file) {

			$postmd = fopen($file,"r") or die("Gick ej att öppna filen");
			$postData = fread($postmd, filesize($file));
			fclose($postmd);

			$postData = ltrim($postData, "--- \n");
			$postData = rtrim($postData, " ---");

			$postDataArray = explode("\n", $postData);

			$empty_keys_array = [];

			foreach($postDataArray as $key => $value) {

				$value = trim($value);

				if($value != '') {
					$splitValue = explode(": ", $value);

					if(!empty($splitValue[1])) {
						$postDataArray[$splitValue[0]] = $splitValue[1];
					}
					else {
						$empty_keys_array[] = $splitValue[0];
					}

				}
				unset($postDataArray[$key]);

			}

			$date = $postDataArray['date'];
			$date = trim($date);
			$date = str_replace("'", "", $date);

			$postDataArray['timestamp'] = strtotime($date);

			foreach($empty_keys_array as $key => $value) {

				if($value == 'taxonomy:') {
					//unset($empty_keys_array[$key]);
				}

				if($value == 'tag:') {
					$tag_value = ltrim($empty_keys_array[$key+1], "- ");
					$postDataArray['tag'] = $tag_value;
				}

				if($value == '---') {
					$tag_value = array_key_exists($key+1, $empty_keys_array) ? $empty_keys_array[$key+1] : false;
					if($tag_value) {
						$postDataArray['comment'] = $tag_value;
					}
				}

			}

			$postDataArray['tagArray'] = [];

			if(array_key_exists('tag', $postDataArray)) {
				$tagString = ltrim($postDataArray['tag'], "[");
				$tagString = rtrim($tagString, "]");

				$tagArray = explode(',', $tagString);
				foreach($tagArray as $tag) {
					if($tag != '') { array_push($postDataArray['tagArray'],$tag); }
				}
			}

			// echo '<pre>';
			// 	print_r($postDataArray);
			// echo '</pre>';

			if(array_key_exists('image', $postDataArray)) {

				$imgUrl = str_replace('post.md', 'image.jpg', $file);
				$imgUrl = str_replace('..', 'http://'.ROOT, $imgUrl);

				$postDataArray['imageUrl'] = $imgUrl;
			}

			return $postDataArray;

		}

		static private function get_random_post($posts) {
			$noofPosts = count($posts)-1;
			$randomValue = rand(0, $noofPosts);
			return $posts[$randomValue];
		}

		public static function delete_dir($dir_path) {
			if (! is_dir($dir_path)) {
				throw new InvalidArgumentException($dir_path.' must be a directory');
			}
			if (substr($dir_path, strlen($dir_path) - 1, 1) != '/') {
				$dir_path .= '/';
			}
			$files = glob($dir_path . '*', GLOB_MARK);
			foreach ($files as $file) {
				if (is_dir($file)) {
					self::delete_dir($file);
				} else {
					unlink($file);
				}
			}
			rmdir($dir_path);
		}

	}