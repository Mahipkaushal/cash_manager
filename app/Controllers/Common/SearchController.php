<?php
namespace App\Controllers\Common;

use App\Controllers\Controller;
use App\Models\Tolist;

class SearchController extends Controller {

	protected $tolist;
	
	public function subSearch($request, $response) {
		$json = array();

		if(!$request->getAttribute('error')){

			$this->tolist = new Tolist($this);

			$results = array();

			$search_term = $request->getParam('sub_search');
			$result_search_id_parts = explode(":", $request->getParam('result_search_id'));

			$type = 0;

			if(isset($result_search_id_parts[0])) {
				$type = $result_search_id_parts[0];
			}

			$filter = array();
			$filter['name'] = $search_term;

			if($type == 5) {				
				$cash_manager_app_to_options_id = 0;
				
				if(isset($result_search_id_parts[1])) {
					$cash_manager_app_to_options_id = $result_search_id_parts[1];
				}

				$filter['cash_manager_app_to_options_id'] = $cash_manager_app_to_options_id;

				$results = $this->tolist->getToOptionVariant($filter);

				if($results) {
					foreach ($results as $result) {
						$json['results'][] = array(
							'id'		=>	$result['cash_manager_app_to_options_variant_id'],
							'text'		=>	$result['name']
						);
					}
				} else {
					$json['no_result'] = 'No result found!';
				}
			}

	    } else {
	    	$json['error']['code'] = 401;
	    	$json['error']['message'] = $request->getAttribute('message');
	    }
        
        return json_encode($json);
	}

}