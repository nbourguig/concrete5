<?
defined('C5_EXECUTE') or die(_("Access Denied."));

class AddressAttributeTypeController extends AttributeTypeController  {

	public $helpers = array('form');
	
	
	public function searchForm($list) {
		$address1 = $this->request('address1');
		$address2 = $this->request('address2');
		$city = $this->request('city');
		$state_province = $this->request('state_province');
		$postal_code = $this->request('postal_code');
		$country = $this->request('country');
		if ($address1) {
			$list->filterByAttribute(array('address1' => $this->attributeKey->getAttributeKeyHandle()), '%' . $address1 . '%', 'like');
		}
		if ($address2) {
			$list->filterByAttribute(array('address2' => $this->attributeKey->getAttributeKeyHandle()), '%' . $address2 . '%', 'like');
		}
		if ($city) {
			$list->filterByAttribute(array('city' => $this->attributeKey->getAttributeKeyHandle()), '%' . $city . '%', 'like');
		}
		if ($state_province) {
			$list->filterByAttribute(array('state_province' => $this->attributeKey->getAttributeKeyHandle()), $state_province);
		}
		if ($postal_code) {
			$list->filterByAttribute(array('postal_code' => $this->attributeKey->getAttributeKeyHandle()), '%' . $postal_code . '%', 'like');
		}
		if ($country) {
			$list->filterByAttribute(array('country' => $this->attributeKey->getAttributeKeyHandle()), $country);
		}
		return $list;
	}

	protected $searchIndexFieldDefinition = array(
		'address1' => 'C 255 NULL',
		'address2' => 'C 255 NULL',
		'city' => 'C 255 NULL',
		'state_province' => 'C 255 NULL',
		'country' => 'C 255 NULL',
		'postal_code' => 'C 255 NULL'
	);
	
	public function search() {
		print $this->form();
		$v = $this->getView();
		$this->set('search', true);
		$v->render('form');
	}

	public function saveForm($data) {
		$this->saveValue($data);
	}

	public function validateForm($data) {
		return ($data['address1'] != '' && $data['city'] != '' && $data['state_province'] != '' && $data['country'] != '' && $data['postal_code'] != '');	
	}	
	
	public function getSearchIndexValue() {
		$v = $this->getValue();
		$args = array();
		$args['address1'] = $v->getAddress1();
		$args['address2'] = $v->getAddress2();
		$args['city'] = $v->getCity();
		$args['state_province'] = $v->getStateProvince();
		$args['country'] = $v->getCountry();
		$args['postal_code'] = $v->getPostalCode();
		return $args;
	}
	
	public function deleteKey() {
		$db = Loader::db();
		$arr = $this->attributeKey->getAttributeValueIDList();
		foreach($arr as $id) {
			$db->Execute('delete from atAddress where avID = ?', array($id));
		}
	}
	public function deleteValue() {
		$db = Loader::db();
		$db->Execute('delete from atAddress where avID = ?', array($this->getAttributeValueID()));
	}
	
	public function saveValue($data) {
		$db = Loader::db();
		if ($data instanceof AddressAttributeTypeValue) {
			$data = (array) $data;
		}
		extract($data);
		$db->Replace('atAddress', array('avID' => $this->getAttributeValueID(),
			'address1' => $address1,
			'address2' => $address2,
			'city' => $city,
			'state_province' => $state_province,
			'country' => $country,
			'postal_code' => $postal_code
			),
			'avID', true
		);
	}

	public function getValue() {
		$val = AddressAttributeTypeValue::getByID($this->getAttributeValueID());		
		return $val;
	}
	
	public function getDisplayValue() {
		$v = $this->getValue();
		$ret = nl2br($v);
		return $ret;
	}
	
	public function action_load_provinces_js() {
		$h = Loader::helper('lists/states_provinces');
		print "var ccm_attributeTypeAddressStatesTextList = '\\\n";
		$all = $h->getAll();
		foreach($all as $country => $countries) {
			foreach($countries as $value => $text) {
				print $country . ':' . $value . ':' . $text . "|\\\n";
			}
		}
		print "'";
	}
	
	public function form() {
		if (is_object($this->attributeValue)) {
			$value = $this->getAttributeValue()->getValue();
			$this->set('address1', $value->getAddress1());
			$this->set('address2', $value->getAddress2());
			$this->set('city', $value->getCity());
			$this->set('state_province', $value->getStateProvince());
			$this->set('country', $value->getCountry());
			$this->set('postal_code', $value->getPostalCode());
		}
		$this->addHeaderItem(Loader::helper('html')->javascript($this->attributeType->getAttributeTypeFileURL('country_state.js')));
		$this->addHeaderItem(Loader::helper('html')->javascript($this->getView()->action('load_provinces_js')));
		$this->set('key', $this->attributeKey);
	}

}

class AddressAttributeTypeValue extends Object {
	
	public static function getByID($avID) {
		$db = Loader::db();
		$value = $db->GetRow("select avID, address1, address2, city, state_province, postal_code, country from atAddress where avID = ?", array($avID));
		$aa = new AddressAttributeTypeValue();
		$aa->setPropertiesFromArray($value);
		if ($value['avID']) {
			return $aa;
		}
	}
	
	public function __construct() {
		$h = Loader::helper('lists/countries');
		$this->countryFull = $h->getCountryName($this->country);		
	}	
	
	public function getAddress1() {return $this->address1;}
	public function getAddress2() {return $this->address2;}
	public function getCity() {return $this->city;}
	public function getStateProvince() {return $this->state_province;}
	public function getCountry() {return $this->country;}
	public function getPostalCode() {return $this->postal_code;}
	public function getFullCountry() {
		$h = Loader::helper('lists/countries');
		return $h->getCountryName($this->country);		
	}
	public function getFullStateProvince() {
		$h = Loader::helper('lists/states_provinces');
		$val = $h->getStateProvinceName($this->state_province);
		if ($val == '') {
			return $this->state_province;
		} else {
			return $val;
		}
	}
	
	public function __toString() {
		$ret = '';
		if ($this->address1) {
			$ret .= $this->address1 . "\n";
		}
		if ($this->address2) {
			$ret .= $this->address2 . "\n";
		}
		if ($this->city) {
			$ret .= $this->city;
		}
		if ($this->city && $this->state_province) {
			$ret .= ", ";
		}
		if ($this->state_province) {
			$ret .= $this->getFullStateProvince();
		}
		if ($this->postal_code) {
			$ret .= " " . $this->postal_code;
		}
		if ($this->city || $this->state_province || $this->postal_code) {
			$ret .= "\n";
		}
		if ($this->country) {
			$ret .= $this->getFullCountry();
		}
		return $ret;		
	}
}