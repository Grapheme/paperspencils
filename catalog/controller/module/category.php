<?php  
class ControllerModuleCategory extends Controller {
	protected function index($setting) {
		$this->language->load('module/category');
		
    	$this->data['heading_title'] = $this->language->get('heading_title');
		
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}
		
		if (isset($parts[0])) {
			$this->data['category_id'] = $parts[0];
		} else {
			$this->data['category_id'] = 0;
		}
		
		if (isset($parts[1])) {
			$this->data['child_id'] = $parts[1];
		} else {
			$this->data['child_id'] = 0;
		}
							
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);
		
		//Показывать или нет количество товаров
		$show_product_count = $this->config->get('config_product_count');

		foreach ($categories as $category) {
			//Будем вычислять кол-во товаров в категориях только если это кол-во надо показывать
			$PIDs=array();
			if ($show_product_count) {
				$res = $this->model_catalog_product->getTotalProductsID(array('filter_category_id' => $category['category_id']));
				foreach ($res as $key=>$value) {
					$PIDs[$value['product_id']]=$value['product_id'];
				}
			}

			$children_data = array();

			$children = $this->model_catalog_category->getCategories($category['category_id']);

			foreach ($children as $child) {
				//Будем вычислять кол-во товаров в категориях только если это кол-во надо показывать
				if ($show_product_count) {
					$data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$res = $this->model_catalog_product->getTotalProductsID($data);
					$product_total=count($res);
					foreach ($res as $key=>$value) {
						$PIDs[$value['product_id']]=$value['product_id'];
					}

//					$total += count($PIDs);
				}

				$children_data[] = array(
					'category_id' => $child['category_id'],
					'name'        => $child['name'] . ($show_product_count ? ' (' . $product_total . ')' : ''),
					'href'        => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])	
				);		
			}

			$total = count($PIDs);

			$this->data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name'] . ($show_product_count ? ' (' . $total . ')' : ''),
				'children'    => $children_data,
				'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
			);	
		}
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/category.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/category.tpl';
		} else {
			$this->template = 'default/template/module/category.tpl';
		}
		
		$this->render();
  	}

}
?>