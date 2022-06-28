<?php 
public function Get_categories_tree($CategoriesNewsID) {
      $arrAllChild = []; // array that will store all children
      while (true) {
          $arrChild = []; // array for storing children in this iteration
          $q = "SELECT `CategoriesNewsID` FROM `categoriesnews` WHERE `Publish` = 1 AND `ParentID` IN (" . $CategoriesNewsID . ")";
          $rs = mysql_query($q);
          while ($r = mysql_fetch_assoc($rs)) {
              $arrChild[] = $r['CategoriesNewsID'];
              $arrAllChild[] = $r['CategoriesNewsID'];
          }
          if (empty($arrChild)) { // break if no more children found
              break;
          }
          $CategoriesNewsID = implode(',', $arrChild); // generate comma-separated string of all children and execute the query again
      }
      return ($arrAllChild);
  }
  private function news_categories_recursion($result = false,$cateNewsActive = false) {
			if($result !== false) {
				$result = $result -> result_array();
				$temp = $result;
				$str = $this -> Categories(0,$temp,$cateNewsActive);
				return $str;
			}
			return "";
		}

		private function Categories($parentid = 0,$array = array(),$cateNewsActive = false){
			$categories = "";
			$temp = array();
			for ($i=0; $i < count($array); $i++) {
				if($array[$i]["ParentID"] == $parentid) $temp[] = $array[$i];
			}
			$categories .= "<ul>";
			if($parentid == 0) {
				$categories .= '<li';
				if($cateNewsActive !== false && $cateNewsActive == 'home') {
					$categories .= ' class="menu-active" ';
				}
				$categories .= ' ><a href="'.base_url().'tin-tuc/">Home</a><ul style="height:36px;"></ul></li>';
			}
			for ($i=0; $i < count($temp); $i++) {
				$categories .= '<li ';
				if($cateNewsActive !== false && $cateNewsActive == $temp[$i]['CategoriesNewsID']) {
					$categories .= ' class="menu-active" ';
				}
				$categories .= ' ><a';
				$categories .= ' href="'.base_url().'tin-tuc/'.$temp[$i]['Slug'].'">'.$temp[$i]["Title"].'</a>';
				$categories .= $this -> Categories($temp[$i]['CategoriesNewsID'],$array);
				$categories .= '</li>';
			}
			$categories .= "</ul>";
			return $categories;
		}
    function Get_main_categories(){
        	global $lang;
			if($lang == 'en'){
				$this -> db -> select('categoriesnews.CategoriesNewsID,categoriesnews.Title_en as Title,categoriesnews.Slug');
			} else if($lang == 'fr'){
				$this -> db -> select('categoriesnews.CategoriesNewsID,categoriesnews.Title_fr as Title,categoriesnews.Slug');
			} else {
				$this -> db -> select('categoriesnews.CategoriesNewsID,categoriesnews.Title,categoriesnews.Slug');
			}
			$this -> db -> from('categoriesnews');
            $this -> db -> where('categoriesnews.Publish', 1);
            $this -> db -> where('categoriesnews.IsHot', 1);
            $this -> db -> where('categoriesnews.ParentID', 0);
            $this -> db -> order_by("Orders","asc");
            $query = $this -> db -> get();
            $parents = $query -> result();
            foreach($parents as $par){
            	$parent_list = $this -> Get_categories_tree($par -> CategoriesNewsID);
            	$parent_list[] = $par -> CategoriesNewsID;
            	$par -> news = $this -> Get_child_news($parent_list,5);
            }            return $parents;
        } 
