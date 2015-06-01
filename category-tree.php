<?php
class CategoryTreeConstructor {

	private $data = array();
	public $rendered;

	public function __construct(&$Input)
	{
		foreach($Input as $Item)
		{
			$Item= (array) $Item;
			$this->data['items'][$Item['category_id']] = $Item;
			$this->data['parents'][$Item['parent_id']][] = $Item['category_id'];
			if(!isset($this->top_level) || $this->top_level > $Item['parent_id'])
			{
				$this->top_level = $Item['parent_id'];
			}
		}
		return $this;
	}

	public function build($id)
	{
		$return{$id} = array();
		foreach($this->data['parents'][$id] as $child)
		{
			$build = $this->data['items'][$child];
			if(isset($this->data['parents'][$child]))
			{
				//$build['has_children'] = true;
				$build['children']	=	array();
				$build['children'] = $this->build($child);
			}
			else
			{
				$build['children']	=	array();
				//$build['has_children'] = false;
			}
			$return{$id}[] = $build;
		}
		return (array) $return{$id};
	}

	public function render()
	{
		if(!isset($this->rendered) || !is_array($this->rendered))
		{
			$this->rendered = $this->build($this->top_level);
		}
		return $this->rendered;
	}
}

public function categoryTable($data, $y)
{
	foreach($data as $cate)
	{
		$this->ret[$this->x][$y] 	=	$cate['category_name'];
		$this->retIds[$this->x][$y]	=	$cate['category_id'];
		//echo count($cate['children']);
		if(count($cate['children']))
		{
			$this->categoryTable($cate['children'], $y+1);			
		}
		$this->x++;
	}
}

public function formCategoryHierarchy($arrAllCatData = array()) {
	$objFF 			= 	new FeedFormat();
	$objTree		= 	new CategoryTreeConstructor($arrAllCatData);
	$arrCategories	=	$objTree->render();
	$this->categoryTable($arrCategories, 0);
	$previous_value = array();	
	$retIdsTemp	=	array();

	foreach ($this->ret as $key => $value)
	{
		$basket = array_replace($previous_value, $value);		
		$value	=	array_splice($basket, 0, (max(array_keys($value)) + 1));
		$arrTemp[]	=	$value;			
		
		$previous_value = $value;		
								
	} 
	//echo "<pre>"; print_r($arrTemp); exit;
	
	$iprevious_value	=	array();
	foreach ($this->retIds as $key => $value)
	{
		$basket = array_replace($iprevious_value, $value);		
		$value	=	array_splice($basket, 0, (max(array_keys($value)) + 1));
		$retIdsTemp[]	=	$value;			
		
		$iprevious_value = $value;											
	}
	
	//echo "<pre>"; print_r($retIdsTemp); exit;
	$arrKeys	=	array();		 
	foreach ($retIdsTemp as $key => $value)
	{	
		$arrNewTmpIds[]	=	$value;
		$arrNewTmp[]	=	$arrTemp[$key];
	}
	//echo "<pre>"; print_r($arrTemp); exit;
	//echo "<pre>"; print_r($arrNewTmp); exit;				
	
	$arrSanitize	=	array();
	$max_level = 0;
	foreach ($arrNewTmp as $key => $value) 
	{
		foreach ($value as $k => $v) 
		{
			$arrSanitize[$key][$k]	=	str_replace(array("&amp;","&amp;amp;"), array("&","&"), $v);
		}
		$max_level	=	max($max_level, count($value));
	}
	$arrBreadcrubm = array();
	if(count($arrSanitize)) {
		foreach($arrSanitize as $arrSanitizeKey => $arrSanitizeVal) {
			$catTaxonomy 		= NULL;
			$catTaxonomy 		= implode(' > ', $arrSanitizeVal);
			$arrBreadcrubm[] 	= array($objFF->xlsStringConvertion($catTaxonomy));
		}
	}

	@sort($arrBreadcrubm,SORT_NATURAL | SORT_FLAG_CASE);
	//echo '<pre>'; print_r($arrBreadcrubm); echo '</pre>'; die;
	return $arrBreadcrubm;
}

?>
