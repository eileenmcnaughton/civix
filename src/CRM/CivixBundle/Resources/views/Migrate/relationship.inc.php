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
class <?php echo $fullName; ?>_Relationship extends <?php echo $fullName; ?>_migrate {
  protected $entity = 'relationship';
  // set to 1 for debug info
  protected $debug = 1;
  protected $base_table_id = 'id'; // name of id field
  protected $base_table_alias = '';
  protected $base_table = '';
  protected $_base_table_string;

  public function __construct($arguments = array()) {
    parent::__construct($arguments);
    if (empty($this->base_table)) {
      $this->setPlaceHolder();
      return;
    }

    // $this->addFieldMapping('contact_id_b', 'relationships_constit_id')->sourceMigration('Contact')->issueGroup('Done');
    // $this->addFieldMapping('contact_id_a', 'relationships_relation_id')->sourceMigration('Contact')->issueGroup('Done');
  }

  public function prepare(&$entity, &$row) {
    $entity->is_active = 1;
    $this->wranglePastRelationshipsToInactive($entity);
    $this->wrangleIncompleteDates($entity);
    //$this->wrangleSpecialRelationshipTypes($entity, $row);


    if (empty($entity->relationship_type_id) && !empty($row->relationships_recip_relation_code)) {
      $mappings = $this->relationshipMappings();
      $reciprocalCode = $row->relationships_recip_relation_code;
      $entity->relationship_type_id = !empty($mappings[$reciprocalCode]) ? $mappings[$reciprocalCode] : $reciprocalCode;
      // we need to do this after filling missing codes AND before wrangleSpecialRelationshipTypes in case
      $this->wranglePastRelationshipsToInactive($entity);
    }

    //$this->useMap($entity, 'relationship_type_id', 'Relationships');
    if (empty($entity->relationship_type_id) && !empty($row->relationships_is_spouse)) {
      $entity->relationship_type_id = 2;
    }
    /*
     * We changed Former Employee to employee but have commented this out
     * to simplify for Protodata
     *
    if ($entity->relationship_type_id == 11 && !empty($entity->end_date)) {
      $entity->relationship_type_id = 5;
      $entity->is_active = 0;
    }

    /*
    if ($entity->relationship_type_id == 15 && !empty($entity->end_date)) {
      $entity->relationship_type_id = 13;
      $entity->is_active = 0;
    }
    */
    if (empty($entity->relationship_type_id) && empty($row->relationships_recip_relation_code)) {
      $entity->relationship_type_id = 16;
    }

    $this->wrangleWrongDirectionRelationships($entity);
    $this->wrangleRepresentative($entity, $row);
    if ($entity->relationship_type_id == 5 || $entity->relationship_type_id == 11) {
      try {
        $contactAType = civicrm_api3('contact', 'getvalue', array(
          'id' => $entity->contact_id_a,
          'return' => 'contact_type'
        ));
        $contactBType = civicrm_api3('contact', 'getvalue', array(
          'id' => $entity->contact_id_b,
          'return' => 'contact_type'
        ));
        if ($contactBType != 'Organization') {
          // Change relationship type to show a person is employing a person.
          $entity->relationship_type_id = 32;
        }
        elseif ($entity->is_active && $entity->relationship_type_id == 5) {
          civicrm_api3('contact', 'create', array(
            'id' => $entity->contact_id_a,
            'employer_id' => $entity->contact_id_b
          ));


        }
        if ($contactAType != 'Individual') {
          if ($contactBType == 'Individual') {
            // B is an individual and A isn't so we assume we swap them around.
            $contactB = $entity->contact_id_b;
            $entity->contact_id_b = $entity->contact_id_a;
            $entity->contact_id_a = $contactB;
          }
          else {
            // If neither are individuals we change the relationship type to misc.
            $entity->relationship_type_id = 29;
          }
        }
      }
      catch (Exception $e) {
        throw new Exception('No contact exists for Razors Edge constituent ' . $row->relationships_constit_id . ' or ' . $row ->relationships_relation_id);
      }
    }

    $this->dedupeRelationship($entity);
  }

  public function prepareRow($row) {

    parent::prepareRow($row);

    if (empty($row->relationships_constit_id) || empty($row ->relationships_relation_id)) {
      throw new Exception('One contact has not been created record ids are ' .  $row->relationships_constit_id . ' or ' . $row ->relationships_relation_id);
    }
  }

  function mapRelationships() {
    return array(
      'Employee' => 5,
      'Former Employee' => 11,
      'Former Employer' => 11,
      'State Electorate' => 12,
      'Past member' => 15,
      'Volunteer' => 6,
      'Service Subsidiary' => 17,
      'Service Parent Org' => 17,
      'Federal Electorate' => 18,
      'Personal Assistant' => 21,
      'Student' => 20,
      'Husband' => 2,
      'SACOSS Rep' => 22,
      'Employer' => 5,
      'Wife' => 2,
      'Policy Advisor' => 23,
      'Sub Committee' => 24,
      'Advisor' => 25,
      'Member' => 13,
      'Chief of Staff' => 26,
      'Media Advisor' => 30,
      'Former Personal Assistant' => 31,
      'Sitting member' => 12,
//29 is misc 32 is person employs person
    );
  }

  function relationshipMappings() {
    return array();
  }

  /**
   * // If they have a primary or representative contact relationship we call it that &
  // put other details into the description (alternative is to manage 2 relationships
  // but at this stage that seems like extra data management for no gain.
   * $sittingMemberTypes = array(12, 33);
   *
   * @param $entity
   * @param $row
   */
  protected function wrangleRepresentative(&$entity, &$row) {
    if (!$row->relationships_contact_type == 'Primary' && !$row->relationships_contact_type == 'Representative'
      //|| in_array($entity->relationship_type_id, $sittingMemberTypes)
    ) {
      return;
    }

    $entity->is_permission_a_b = TRUE;
    $entity->is_permission_b_a = TRUE;

    $membershipCount = civicrm_api3('membership', 'getcount', array(
      'contact_id' => $entity->contact_id_b,
      'active_only' => TRUE,
    ));


    if (!empty($entity->relationship_type_id)) {
      if (!empty($entity->description)) {
        $entity->description = ' (' . $entity->description . ')';
      }
      $relationshipType = str_replace('Employer', 'Employee', $entity->relationship_type_id);
      if ($relationshipType == 16) {
        $relationshipType = '';
      }
      if ($entity->relationship_type_id == 5) {
        $relationshipType = 'Employee';
      }
      $entity->description = $relationshipType . $entity->description;
    }

    if ($row->relationships_contact_type == 'Primary') {
      $entity->relationship_type_id = 28;
    }

    if ($membershipCount) {
      if ($row->relationships_contact_type == 'Primary') {
        $repParams = (array) $entity;
        if (isset($repParams['id'])) {
          unset($repParams['id']);
        }
        $repParams['relationship_type_id'] = 27;
        $repParams['return'] = 'id';
        // If they are a member then the primary contact should be a primary contact AND a voting rep.
        try {
          $repParams['id'] = civicrm_api3('relationship', 'getvalue', $repParams);
        }
        catch (Exception $e) {
          // No id
        }
        civicrm_api3('relationship', 'create', $repParams);
        $entity->relationship_type_id = 28;
      }
      elseif ($row->relationships_contact_type == 'Representative') {
        $entity->relationship_type_id = 27;
      }

    }
    elseif ($row->relationships_contact_type == 'Representative') {
      // Strip any voting reps
      $entity->relationship_type_id = 5;
      $entity->is_permission_a_b = FALSE;
      $entity->is_permission_b_a = FALSE;
      $entity->description = '(was representative) ' . $entity->description;
    }
  }

  /**
   * @param $entity
   */
  protected function wrangleIncompleteDates(&$entity) {
    if (!empty($entity->end_date) && strlen($entity->end_date) < 8) {
      $entity->end_date = str_pad($entity->end_date, 8, '01');
    }
    if (!empty($entity->start_date) && strlen($entity->start_date) < 8) {
      $entity->start_date = str_pad($entity->start_date, 8, '01');
    }
  }

  /**
   * @param $entity
   * @param $row
   */
  protected function wrangleSpecialRelationshipTypes(&$entity, &$row) {

    // Sitting Members with a reciprocal relation code should have ‘Sitting State Member is / State Electorate is’
    // whereas Sitting Members without a reciprocal relation code should have ‘Sitting Member is / Sitting Member for’.
    if ($entity->relationship_type_ref == 'State Electorate' &&
      (!empty($row->relationships_recip_relation_code) || !empty($row->relationships_relation_code)
      ) || ($entity->relationship_type_id == 'Sitting member' &&
       $row->relationships_recip_relation_code == 'State Electorate')
    ) {
      $entity->relationship_type_id = 33;
    }

// Sitting Members with a reciprocal relation code should have ‘Sitting State Member is / State Electorate is’
    // whereas Sitting Members without a reciprocal relation code should have ‘Sitting Member is / Sitting Member for’.
    if (empty($entity->relationship_type_id) || $entity->relationship_type_id == 'Sitting member') {
      if ($row->relationships_recip_relation_code == 'Sitting member'
        && !empty($row->relationships_relation_code)
      ) {
        $entity->relationship_type_id = 33;
      }
      elseif ($entity->relationship_type_id == 'Sitting member') {
        $entity->relationship_type_id = 12;
      }
      elseif (!empty($row->relationships_is_employee)) {
        $entity->relationship_type_id = 5;
      }
    }
  }

  /**
   * @param $entity
   */
  protected function wrangleWrongDirectionRelationships(&$entity) {
    static $contactBOrgRelationships = array();
    if (empty($contactBOrgRelationships)) {
      $relationResult = civicrm_api3('relationship_type', 'get', array('contact_type_b' => 'Organization'));
      $contactBOrgRelationships = array_keys($relationResult['values']);
    }

    if (in_array($entity->relationship_type_id, $contactBOrgRelationships)) {
      if (civicrm_api3('contact', 'getvalue', array(
          'id' => $entity->contact_id_b,
          'return' => 'contact_type'
        )) != 'Organization'
      ) {
        $b = $entity->contact_id_b;
        $entity->contact_id_b = $entity->contact_id_a;
        $entity->contact_id_a = $b;
      }
    }
  }

  /**
   * @param $entity
   */
  protected function dedupeRelationship(&$entity) {
// Reduce error output by not giving errors at this point
    // by linking with existing ID
    $dates = array('start_date', 'end_date');
    if (empty($entity->id)) {
      try {
        $checkParams = array_merge((array) $entity, array('return' => 'id, start_date, end_date'));
        unset($checkParams['description']);
        if ($entity->relationship_type_id == 5) {
          unset($checkParams['start_date'], $checkParams['end_date']);
        }
        $result = civicrm_api3('relationship', 'getsingle', $checkParams);
        if (!in_array($entity->relationship_type_id, array(5))) {
          foreach ($dates as $dateField) {
            if (empty($checkParams[$dateField])) {
              // $checkParams[$dateField] = array('IS NULL' => TRUE);
              if (!empty($result[$dateField])) {
                throw new Exception ('not a real match');
              }
            }
          }
        }
        $entity->id = $result['id'];
      }
      catch (Exception $e) {

        $checkParams['contact_id_a'] = $entity->contact_id_b;
        $checkParams['contact_id_b'] = $entity->contact_id_a;
        try {
          $result = civicrm_api3('relationship', 'getsingle', $checkParams);
          foreach ($dates as $dateField) {
            if (empty($checkParams[$dateField])) {
              // $checkParams[$dateField] = array('IS NULL' => TRUE);
              if (!empty($result[$dateField])) {
                throw new Exception ('not a real match');
              }
            }
          }
          $entity->id = $result['id'];

          $entity->contact_id_a = $checkParams['contact_id_a'];
          $entity->contact_id_b = $checkParams['contact_id_b'];
        }
        catch (Exception $e) {
          // It must be unique then.
        }
      }
    }
  }

  /**
   * @param $entity
   */
  protected function wranglePastRelationshipsToInactive(&$entity) {
    if (stristr(strtolower($entity->relationship_type_ref), 'past') || stristr(strtolower($entity->relationship_type_ref), 'former')) {
      $entity->is_active = 0;
    }
  }

}
