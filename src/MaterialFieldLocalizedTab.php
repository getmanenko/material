<?php
namespace samsoncms\app\material;

use samson\core\SamsonLocale;

/** 
 * @author Egorov Vitaly <egorov@samsonos.com>
 */
class MaterialFieldLocalizedTab extends FormTab
{
    /** Meta static variable to disable default form rendering */
    public static $AUTO_RENDER = false;

    /** Tab sorting index for header sorting */
    public $index = 1;

    /**
     * Field DB object pointer
     * @var \samson\activerecord\field
     */
    protected $db_field;

    /**
     * Constructor
     * @param Form $form
     * @param \samson\activerecord\field $db_field
     * @param string $locale
     */
    public function __construct(Form & $form, & $db_field, $field_type)
    {
        // Set field header name
        $this->name = !empty($db_field->Description) ? $db_field->Description : $db_field->Name;

        // Save pointers to database field object
        $this->db_field = &$db_field;

        // Call parent
        parent::__construct($form);

        // Prepare locales array with one default locale by default
        $locales = array('');
        // If field supports localization - set full locales array
        if ($db_field->local == 1) $locales = SamsonLocale::$locales;

        // Iterate defined locales
        if (sizeof(SamsonLocale::$locales)) foreach ($locales as $locale) {
            // Try to find existing MaterialField record
            if (!dbQuery('\samsoncms\api\MaterialField')
                ->MaterialID($form->material->id)
                ->FieldID($db_field->id)
                ->locale($locale)
                ->first($db_mf)
            ) {
                // Create MaterialField record
                $db_mf = new \samsoncms\api\MaterialField(false);
                $db_mf->Active = 1;
                $db_mf->MaterialID = $this->form->material->id;
                $db_mf->FieldID = $db_field->id;
                $db_mf->locale = $locale;
                $db_mf->save();
            }

            //elapsed($this->name.'-'.$locale.'-'.$db_mf->Value.'-'.$db_mf->id);

            // Add child tab
            $this->tabs[] = new MaterialFieldTab($form, $this, $db_mf, $locale, $field_type);
        }
    }
}