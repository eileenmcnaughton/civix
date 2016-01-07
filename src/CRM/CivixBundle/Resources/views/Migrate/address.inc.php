<?php
echo "<?php\n";
?>

/**
 * @file
 * A basic migration example.
 */

/**
 * Comments here are limited to CiviMigrate extension - more information is available in
 * the migrate_example module
 *
 * This is a very simple example drawing from a single table
 */
class <?php echo $fullName; ?>_Address extends <?php echo $fullName; ?>_migrate {

  protected $base_table_id = 'id'; // name of id field
  protected $base_table_alias = '<?php
$base_table = !empty($table_map['address']) ? $table_map['contact'] : "";
echo $base_table;
?>';
  protected $base_table = '<?php
$base_table = !empty($table_map['address']) ? $table_map['contact'] : "";
echo $base_table;
?>';
  protected $_base_table_string;
  protected $entity = 'address'; // this is the default
  // Set to 1 for debug info
  protected $debug = 1;

  public function __construct($arguments = array()) {
    parent::__construct($arguments);
    if (empty($this->base_table)) {
      $this->setPlaceHolder();
      return;
    }

    $this->description = t('Import Addresses');

    $this->addFieldMapping('contact_id', '<?php
$base_table = '';
if (!empty($table_map['contact'])) {
  if (isset($table_map['address']) && $table_map['contact'] == $table_map['address']) {
    $base_table = '';
  }
  else {
    $base_table = $table_map['contact'] . '_';
  }
}
echo $base_table;
?>id')->sourceMigration('Contact');

    // See contact for notes - you can map in here or in the civicrm_migrate_mappings table.
    // $this->addFieldMapping('city')->issueGroup('To be mapped');
    // $this->addFieldMapping('location_type_id')->defaultValue('Main')->issueGroup('Done');
  }

  /**
   * @param $entity
   * @param $row
   */
  function prepare(&$entity, &$row) {
    parent::prepare($entity, $row);
    $this->fixStreetAddress($entity);
    $this->fixCity($entity);
    $this->fixCountry($entity);
    if (!empty($entity->street_address) && strlen($entity->street_address) > 128) {
      // Add some handling.
    }
    // potentially call $this->syncEmployerAddress($entity); for shared address handling.
  }

  /**
   * Check if 2 addresses are equivalent.
   *
   * @param string $address1
   * @param string $address1
   *
   * @return bool
   */
  function addressCompare($address1, $address2) {
    if(str_replace(' ', '', str_replace(',','', strtolower($this->addressTidy($address1)))) == str_replace(' ', '', str_replace(',', '', strtolower($this->addressTidy($address2)))) ) {
      return TRUE;
    }
  }

  /**
   * Standardise abbreviations
   */
  function addressTidy($addressField) {
    $pattern = array('/(ave)$/i', '/(st)$/i', '/(rd)$/i', '/(tce)$/i', '/(dr)$/i', '/(p o box)/i', '/(st east)$/i', '/( mt )/i', '/(hwy)$/i', '/(st,)$/i');
    $replacement = array('Avenue', 'Street', 'Road', 'Terrace', 'Drive', 'PO Box', 'Street East', 'Mount', 'Highway', 'Street');
    return preg_replace($pattern, $replacement, $addressField);
  }

  /**
   * @param $entity
   */
  protected function fixStreetAddress(&$entity) {
    $streetAddressFields = array('street_address', 'supplemental_address_1');
    foreach ($streetAddressFields as $streetAddressField) {
      if (!empty($entity->$streetAddressField)) {
        $entity->$streetAddressField = $this->addressTidy($entity->$streetAddressField);
      }
    }
  }

  /**
   * Check for existing address that matches.
   *
   * @param stdObj $entity
   * @param int $contact_id
   *
   * @throws \MigrateException
   */
  function checkExisting($entity, $contact_id){
    if(empty($entity->street_address)) {
      return;
    }
    if (empty($entity->id)){
      $existing = civicrm_api('address', 'get', array(
        'version' => 3,
        'contact_id' => $contact_id,
      ));
      if($existing['count'] > 0) {
        foreach($existing['values'] as $address) {
          if($this->addressCompare($entity->street_address, CRM_Utils_Array::value('street_address', $address))) {
            return $address['id'];
          }
        }
      }
    }
  }

  /**
   * Fix common misspellings.
   */
   function mapNZCity() {
     return array(
       'Auckalnd' => 'Auckland',
       'Auckkland' => 'Auckland',
       'Blenhiem' => 'Blenheim',
       'Bkenheim' =>  'Blenheim',
       'Christrchurch' => 'Christchurch',
       'Chriustchurch' => 'Christchurch',
       'Christhchurch' => 'Christchurch',
       'Christchurcb' => 'Christchurch',
       'Chrstchurch' => 'Chrstchurch',
       'Dannievirke' => 'Dannevirke',
       'Duendin' => 'Dunedin',
       'unedin' => 'Dunedin',
       'Eddendale' => 'Edendale',
       'Feidling' => 'Feilding',
       'Fielding' => 'Feilding',
       'Gisborn' => 'Gisborne',
       'Hamiilton' => 'Hamilton',
       'Hamitlon' => 'Hamilton',
       'Katitai' => 'Kaitaia',
       'Napoer' => 'Napier',
       'Napier Hawkes Bay' => 'Napier',
       'NEW PLYMOUTH' => 'New Plymouth',
       'invercargill' => 'Invercargill',
       'Paekakaraki' => 'Paekakariki',
       'Plamerston North' => 'Palmerston North',
       'Ricmhond' => 'Richmond',
       'Waikane' => 'Waikanae',
       'Wellngton' => 'Wellington',
       'Wellingtno' => 'Wellington',
       'Wellkington' => 'Wellington',
       'Welkington' => 'Wellington',
       'Wellintgon' => 'Wellington',
       'wellington' => 'Wellington',
     );
   }

  function fixCity(&$entity) {
    $this->useMap($entity, 'city', 'NZCity');
  }

  /**
   * Add country where we can guess it.
   *
   * @param $entity
   *
   * @throws \Exception
   */
  function fixCountry(&$entity) {
    if (empty($entity->country)) {
      $entity->country = $this->getCountryByCity($entity->city);
      if (empty($entity->country)) {
        if (($entity->country = $this->getCountryByCity(ucfirst(strtolower($entity->city)))) != FALSE) {
          $entity->city = ucfirst(strtolower($entity->city));
        }
      }
    }
  }

  function getCountryByCity($city) {
    $cityCountryMap = array(
      'Ahipara' => 'NZ',
      'Akaroa' => 'NZ',
      'Albany' => 'NZ',
      'Alexandra' => 'NZ',
      'Amberley' => 'NZ',
      'Apiti' => 'NZ',
      'Arrowtown' => 'NZ',
      'Ashburton' => 'NZ',
      'Ashhurst' => 'NZ',
      'Awanui' => 'NZ',
      'Auckland' => 'NZ',
      'Balclutha' => 'NZ',
      'Banks Peninsula' => 'NZ',
      'Barrys Bay' => 'NZ',
      'Baylys Beach' => 'NZ',
      'Bay of Plenty' => 'NZ',
      'Bethells Beach' => 'NZ',
      'Blackball' => 'NZ',
      'Blenheim' => 'NZ',
      'Bluff' => 'NZ',
      'Brightwater' => 'NZ',
      'Brighton' => 'NZ',
      'Bunnythorpe' => 'NZ',
      'Cable Bay' => 'NZ',
      'Cambridge' => 'NZ',
      'Canterbury' => 'NZ',
      'Carterton' => 'NZ',
      'Central North Island' => 'NZ',
      'Cheviot' => 'NZ',
      'Christchurch' => 'NZ',
      'Clarks Beach' => 'NZ',
      'Clifton' => 'NZ',
      'Clive' => 'Clive',
      'Clyde' => 'Clyde',
      'Collingwood' => 'NZ',
      'Coopers Beach' => 'NZ',
      'Coromandel' => 'NZ',
      'Cromwell' => 'NZ',
      'Culverden' => 'NZ',
      'Dannevirke' => 'NZ',
      'Darfield' => 'NZ',
      'Dargaville' => 'NZ',
      'Diamond Harbour' => 'NZ',
      'Doyleston' => 'NZ',
      'Drury' => 'NZ',
      'Dunedin' => 'NZ',
      'Edendale' => 'NZ',
      'Eltham' => 'NZ',
      'Fairlie' => 'NZ',
      'Featherston' => 'NZ',
      'Feilding' => 'NZ',
      'Forest Lake' => 'NZ',
      'Foxton' => 'NZ',
      'Frasertown' => 'NZ',
      'Franz Josef Glacier' => 'NZ',
      'Geraldine' => 'NZ',
      'Gisborne' => 'NZ',
      'Glendowie' => 'NZ',
      'Golden Bay' => 'NZ',
      'Gore' => 'NZ',
      'Great Barrier Island' => 'NZ',
      'Greytown' => 'NZ',
      'Greymouth' => 'NZ',
      'Hamilton' => 'NZ',
      'Hampden' => 'NZ',
      'Hawarden' => 'NZ',
      'Hawkes Bay' => 'NZ',
      'Haruru Falls' => 'NZ',
      'Hastings' => 'NZ',
      'Havelock' => 'NZ',
      'Havelock North' => 'NZ',
      'Haumoana' => 'NZ',
      'Hector' => 'NZ',
      'Helensville' => 'NZ',
      'Hokitika' => 'NZ',
      'Horotiu' => 'NZ',
      'Huntly' => 'NZ',
      'Hawera' => 'NZ',
      'Hikurangi' => 'NZ',
      'Himatangi Beach' => 'NZ',
      'Hunterville' => 'NZ',
      'Inglewood' => 'NZ',
      'Kaeo' => 'NZ',
      'Kakanui' => 'NZ',
      'Kaiapoi' => 'NZ',
      'Kaikohe' => 'NZ',
      'Kaikoura' => 'NZ',
      'Kaingaroa' => 'NZ',
      'Kaitaia' => 'NZ',
      'Kaiteriteri' => 'NZ',
      'Kaiwaka' => 'NZ',
      'Kapiti Coast' => 'NZ',
      'Karangahake' => 'NZ',
      'Karitane' => 'NZ',
      'Kaitangata' => 'NZ',
      'Katikati' => 'NZ',
      'Kawakawa' => 'NZ',
      'Kawakawa Bay' => 'NZ',
      'Kawerau' => 'NZ',
      'Kawera' => 'NZ',
      'Kawerua' => 'NZ',
      'Kawhia' => 'NZ',
      'Kaukapakapa' => 'NZ',
      'Kirwee' => 'NZ',
      'Kumeu' => 'NZ',
      'Inglewood' => 'NZ',
      'Invercargill' => 'NZ',
      'Kaipaki' => 'NZ',
      'Kaitaia' => 'NZ',
      'Kaweka' => 'NZ',
      'Kerikeri' => 'NZ',
      'Kohukohu' => 'NZ',
      'Leeston' => 'NZ',
      'Levin' => 'NZ',
      'Lincoln' => 'NZ',
      'Little River' => 'NZ',
      'Lower Moutere' => 'NZ',
      'Lyttleton' => 'NZ',
      'Little Kaiteriteri' => 'NZ',
      'Lower Hutt' => 'NZ',
      'Lyttelton' => 'NZ',
      'Mahia' => 'NZ',
      'Manaia' => 'NZ',
      'Matangi' => 'NZ',
      'Maketu' => 'NZ',
      'Mapua' => 'NZ',
      'Mangaweka' => 'NZ',
      'Mangawhai' => 'NZ',
      'Manutuke' => 'NZ',
      'Manatuke' => 'NZ',
      'Marlborough' => 'NZ',
      'Marton' => 'NZ',
      'Martinborough' => 'NZ',
      'Masterton' => 'NZ',
      'Matakana Island' => 'NZ',
      'Mataura' => 'NZ',
      'Maungaturoto' => 'NZ',
      'Matamata' => 'NZ',
      'Mangonui' => 'NZ',
      'Merton' => 'NZ',
      'Millers Flat' => 'NZ',
      'Milton' => 'NZ',
      'Moerewa' => 'NZ',
      'Momona' => 'NZ',
      'Morrinsville' => 'NZ',
      'Mount Maunganui' => 'NZ',
      'Mosgiel' => 'NZ',
      'Motueka' => 'NZ',
      'Murchison' => 'NZ',
      'Murupara' => 'NZ',
      'Napier' => 'NZ',
      'Nelson' => 'NZ',
      'Newport' => 'NZ',
      'New Plymouth' => 'NZ',
      'Ngongotaha' => 'NZ',
      'Nightcaps' => 'NZ',
      'Ngaruawahia' => 'NZ',
      'Norsewood' => 'NZ',
      'North Canterbury' => 'NZ',
      'Nuhaka' => 'NZ',
      'Oamaru' => 'NZ',
      'Ohau' => 'NZ',
      'Okaihou' => 'NZ',
      'Ohope' => 'NZ',
      'Ohaupo' => 'NZ',
      'Ohura' => 'NZ',
      'Okaihau' => 'NZ',
      'Oakihau' => 'NZ',
      'Okaihu' => 'NZ',
      'Omokoroa' => 'NZ',
      'Opotiki' => 'NZ',
      'Opua' => 'NZ',
      'Orewa' => 'NZ',
      'Opunake' => 'NZ',
      'Otago' => 'NZ',
      'Otane' => 'NZ',
      'Otaki' => 'NZ',
      'Otautau' => 'NZ',
      'Otorohanga' => 'NZ',
      'Owaka' => 'NZ',
      'Oxford' => 'NZ',
      'Pahiatua' => 'NZ',
      'Papamoa' => 'NZ',
      'Patea' => 'NZ',
      'Pakotai' => 'NZ',
      'Parakai' => 'NZ',
      'Praparaumu' => 'NZ',
      'Pauatahanui' => 'NZ',
      'Paekakariki' => 'NZ',
      'Palmerston' => 'NZ',
      'Pegasus' => 'NZ',
      'Picton' => 'NZ',
      'Piha' => 'NZ',
      'Plimmerton' => 'NZ',
      'Prebbleton' => 'NZ',
      'Paeroa' => 'NZ',
      'Paihia' => 'NZ',
      'Paihiatua' => 'NZ',
      'Palmerston North' => 'NZ',
      'Paraparaumu' => 'NZ',
      'Patutahi' => 'NZ',
      'Pokeno' => 'NZ',
      'Porirua' => 'NZ',
      'Pirongia' => 'NZ',
      'Pleasant Point' => 'NZ',
      'Pukerua Bay' => 'NZ',
      'Pukenui' => 'NZ',
      'Putaruru' => 'NZ',
      'Queenstown' => 'NZ',
      'Raglan' => 'NZ',
      'Rakaia' => 'NZ',
      'Rai Valley' => 'NZ',
      'Ranfurly' => 'NZ',
      'Rangiora' => 'NZ',
      'Ratana Pa' => 'NZ',
      'Ratana' => 'NZ',
      'Raumati' => 'NZ',
      'Raumati Beach' => 'NZ',
      'Rawene' => 'NZ',
      'Renwick' => 'NZ',
      'Reefton' => 'NZ',
      'Richmond' => 'NZ',
      'Riverhead' => 'NZ',
      'Riverton' => 'NZ',
      'Riwaka' => 'NZ',
      'Runanga' => 'NZ',
      'Rolleston' => 'NZ',
      'Rotorua' => 'NZ',
      'Roxburgh' => 'NZ',
      'Ruakituri' => 'NZ',
      'Ruby Bay' => 'NZ',
      'Russell' => 'NZ',
      'Sanson' => 'NZ',
      'Shannon' => 'NZ',
      'Silverdale' => 'NZ',
      'Southbridge' => 'NZ',
      'Southland' => 'NZ',
      'Spring Creek' => 'NZ',
      'Stirling' => 'NZ',
      'Stratford' => 'NZ',
      'Strathmore' => 'NZ',
      'Sunnyhills' => 'NZ',
      'Taihape' => 'NZ',
      'Taipa' => 'NZ',
      'Tairua' => 'NZ',
      'Takaka' => 'NZ',
      'Tapanui' => 'NZ',
      'Tapaunui' => 'NZ',
      'Titoki' => 'NZ',
      'Taupo' => 'NZ',
      'Tuakau' => 'NZ',
      'Tauranga' => 'NZ',
      'Taumarunui' => 'NZ',
      'Tauwhare' => 'NZ',
      'Te Araroa' => 'NZ',
      'Te Aroha' => 'NZ',
      'Te Awanga' => 'NZ',
      'Te Awamutu' => 'NZ',
      'Te Hana' => 'NZ',
      'Te Horo' => 'NZ',
      'Te Karaka' => 'NZ',
      'Te Kauwhata' => 'NZ',
      'Te Kuiti' => 'NZ',
      'Te Kopuru' => 'NZ',
      'Te Puke' => 'NZ',
      'Temuka' => 'NZ',
      'Timaru' => 'NZ',
      'Tikitiki' => 'NZ',
      'Tirau' => 'NZ',
      'Thames' => 'NZ',
      'Tokoroa' => 'NZ',
      'Tokomaru' => 'NZ',
      'Tokomaru Bay' => 'NZ',
      'Tolaga Bay' => 'NZ',
      'Trentham' => 'NZ',
      'Tuai' => 'NZ',
      'Turangi' => 'NZ',
      'Upper Hutt' => 'NZ',
      'Upper Moutere' => 'NZ',
      'Urenui' => 'NZ',
      'Wakefield' => 'NZ',
      'Waiheke Island' => 'NZ',
      'Waihi Beach' => 'NZ',
      'Waihi' => 'NZ',
      'Waikanae Beach' => 'NZ',
      'Waimate' => 'NZ',
      'Waimana' => 'NZ',
      'Waimauku' => 'NZ',
      'Waipawa' => 'NZ',
      'Waipukurau' => 'NZ',
      'Wairoa' => 'NZ',
      'Wairarapa' => 'NZ',
      'Waitara' => 'NZ',
      'Waitati' => 'NZ',
      'Waiuku' => 'NZ',
      'Waikuku Beach' => 'NZ',
      'Waimangaroa' => 'NZ',
      'Wallacetown' => 'NZ',
      'Wanganui' => 'NZ',
      'Waikane' => 'NZ',
      'Waikanae' => 'NZ',
      'Waikouaiti' => 'NZ',
      'Waimuku' => 'NZ',
      'Waipu' => 'NZ',
      'Waitakere' => 'NZ',
      'Waitomo' => 'NZ',
      'Waitomo Caves' => 'NZ',
      'Waiwera' => 'NZ',
      'Wanaka' => 'NZ',
      'Warkworth' => 'NZ',
      'Warrington' => 'NZ',
      'Wellington' => 'NZ',
      'Wellsford' => 'NZ',
      'West Melton' => 'NZ',
      'Westport' => 'NZ',
      'Whakatane' => 'NZ',
      'Whakatu' => 'NZ',
      'Whangamata' => 'NZ',
      'Whanganui' => 'NZ',
      'Wanganui' => 'NZ',
      'Whangaparaoa' => 'NZ',
      'Whangarei' => 'NZ',
      'Whitianga' => 'NZ',
      'Winton' => 'NZ',
      'Woodend' => 'NZ',
      'Woodville' => 'NZ',
    );
    if (!empty($cityCountryMap[$city])) {
      return $cityCountryMap[$city];
    }
  }

  /**
   * Add address to employer & make a shared address.
   *
   * @param stdObj $entity
   */
  protected function syncEmployerAddress(&$entity) {
    try {
      $employerID = civicrm_api3('relationship', 'getvalue', array(
        'contact_id_a' => $entity->contact_id,
        'return' => 'contact_id_b',
        'is_active' => 1,
        'relationship_type_id' => 5,
      ));
      $entity->master_id = $this->checkExisting($entity, $employerID);
      if (empty($entity->master_id)) {
        $employerAddress = civicrm_api3('address', 'create', array_merge((array) $entity, array(
          'contact_id' => $employerID,
          'id' => NULL,
        )));
        $entity->master_id = $employerAddress['id'];
      }
    } catch (Exception $e) {
      // We tried :-)
    }
  }


}
