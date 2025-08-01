<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('OES Monadic Exploration', 'oes'); ?></h1>
    </div>
</div>
<div class="oes-page-body">
    <div>
        <h2><?php _e('Credits', 'oes'); ?></h2>
        <p><?php
            printf(__('This is an adaptation of Marian Dörks work on Monadic Exploration. The code is open source ' .
                'published and has been adapted for OES purposes. Visit Marian Dörks website for more information ' .
                'on the open source code:  %shttps://mariandoerk.de/monadicexploration/%s.', 'oes'),
            '<a href="https://mariandoerk.de/monadicexploration/" target="_blank">',
            '</a>'
            );
            ?></p>
        <p><?php
            _e('Monadic exploration is a new approach to interacting with relational information spaces that ' .
                'challenges the distinction between the whole and its parts. Building on the work of sociologists ' .
                'Gabriel Tarde and Bruno Latour we turn to the concept of the monad as a useful lens on online ' .
                'communities and collections that expands the possibility for creating meaning in their navigation. ' .
                'While existing interfaces tend to emphasize either the structure of the whole or details of a part, ' .
                'monadic exploration brings these opposing perspectives closer together in continuous movements ' .
                'between partially overlapping points of view. The resulting visualization reflects a given node’s ' .
                'relative position within a network using radial displacements and visual folding.', 'oes'); ?>
        </p>
    </div>
    <div>
        <h2><?php _e('Shortcode', 'oes'); ?></h2>
        <p><?php
            _e('To display data as a monadic exploration you can add a shortcode to a page. The shortcode ' .
                'includes parameters that determine which data will be used to display the monadic exploration.',
                'oes');
            ?>
        </p>
        <p><?php
            _e('The shortcode will look something like this (every thing in curved brackets depends on the ' .
                'projects data model and can be configured):', 'oes');
            ?>
        </p>
        <code>
            [oes_monadic_exploration object1="{post type key}" categorise1="{taxonomy key}" cat1="{slug;Singular;Plural;color}" fields1="{field key 1;field key 2;field key 3}" object_center_text="{option}"]
        </code>
        <p><?php
            _e('The shortcode is always defined within square brackets <code>[]</code>, starts with ' .
                '<code>oes_monadic_exploration</code> and is followed by optional parameters that form a pair of ' .
                'key and value noted as <code>{key}="{value}"</code>.',
                'oes');
            ?>
        </p>
    </div>
    <div>
        <p><strong><?php _e('Objects', 'oes'); ?></strong></p>
        <p><?php
            _e('The objects define the OES post types that are to be displayed as monads. To display data ' .
                'as monadic exploration at least one object has to be defined. The first considered object is ' .
                'marked by <code>object1</code>, the second by <code>object2</code> and so on The value of an object ' .
                'is the <code>{post type key}</code> of the considered OES post type.', 'oes');
            ?>
        </p>
    </div>
    <div>
        <p><strong><?php _e('Categorization of the Considered Object', 'oes'); ?></strong></p>
        <p><?php
            _e('Each object can be divided into several subcategories using a taxonomy. A valid taxonomy is ' .
                'specified for the <code>object1</code> in the parameter <code>categorised1</code>, according to ' .
                'which the associated posts for this object post type are grouped. For an <code>object2</code>, ' .
                'this is defined in the parameter <code>categorised2</code> and so on. Each Monad can only be ' .
                'assigned to one category. If an object has multiple terms for a chosen category, the first term ' .
                'will be considered and other terms will be ignored.', 'oes');
            ?>
        </p>
    </div>
    <div>
        <p><strong><?php _e('Categories', 'oes'); ?></strong></p>
        <p><?php
            _e('Categories determine the group and color in which a monad is displayed. The groups are arranged ' .
                'consecutively in a circle in a clockwise direction. The first category is defined by ' .
                '<code>cat1</code>, the value of <code>cat1</code> consists of several parameters separated by a ' .
                'semicolon. The first parameter is the “category slug”. Valid slugs are post type keys and taxonomy ' .
                'keys. If an object is also sorted by category, a term can also be used as a slug, for which the ' .
                'category must be specified as <code>{taxonomy key}_{term ID}</code>. The slug “none” is used for ' .
                'objects without assignment to one of the previous slugs. ' .
                'The second parameter describes the singular of the category, the third the plural. If there is no ' .
                'third parameter, the singular is also used for the plural. The fourth parameter specifies the color ' .
                'value of the category in HEX notation. If the parameter has not been set, black ' .
                '(<code>#111111</code>) is set. Further categories follow this notation and are defined by ' .
                '<code>cat2</code>, <code>cat3</code>, and so on.',
                'oes');
            ?>
        </p>
    </div>
    <div>
        <p><strong><?php _e('Connected Data of Objects (Fields)', 'oes'); ?></strong></p>
        <p><?php
            _e('Fields and taxonomies can be assigned to each object in order to display linked data objects ' .
                'as monads as well. For <code>object1</code>, the fields are defined in the <code>fields1</code> ' .
                'parameter, for <code>object2</code> in the <code>fields2</code> parameter and so on. The value of ' .
                'the parameter is a semicolon-separated list of field keys and taxonomies. Only fields of type ' .
                '"relationship", "post_object" or "taxonomy" are taken into account. If a field of this type is to ' .
                'be included, it must appear in the list with <code>field__{field key}</code> (there are two ' .
                'underscores between <code>field</code> and <code>{field_key}</code>). If the field of a parent ' .
                'object is to be taken into account, this is noted by <code>parent_field__{parent field key}</code>. ' .
                'A taxonomy can be added via <code>taxonomy__{taxonomy key}</code> and a taxonomy of the parent ' .
                'object via <code>parent_taxonomy__{taxonomy key}</code>.', 'oes');

            ?></p>
    </div>
    <div>
        <p><strong><?php _e('Title', 'oes'); ?></strong></p>
        <p><?php
            _e('This title of a monad can be specified by the parameter <code>{post type key}_title_text</code> ' .
                'or <code>{taxonomy key}_title_text</code>. If no value or the value “title” is specified ' .
                'the display title of the object (as defined in the OES settings) is used. ' .
                'A static string can ' .
                'be output via <code>string:{string text}</code> for all objects of this object type. To output the ' .
                'value of a field of the displayed post or term, the value must be specified as ' .
                '<code>{field key}</code>.', 'oes')
            ?></p>
    </div>
    <div>
        <p><strong><?php _e('Center Text', 'oes'); ?></strong></p>
        <p><?php
            _e('If a monad is selected in the frontend display, a text can be output in addition to the title, ' .
                'which should not be longer than 300 characters for display reasons. This text output can be ' .
                'specified for each post type and taxonomy. The parameter <code>{post type key}_center_text</code> ' .
                'or <code>{taxonomy key}_center_text</code> can be given a value that defines the text output. If no ' .
                'value or the value “default” is specified, the first 300 characters of the post content (without ' .
                'formatting) are output in the case of a post and nothing in the case of a term. A static string can ' .
                'be output via <code>string:{string text}</code> for all objects of this object type. To output the ' .
                'value of a field of the displayed post or term, the value must be specified as ' .
                '<code>{field key}</code>.', 'oes')
            ?></p>
    </div>
    <div>
        <p><strong><?php _e('Further Options', 'oes'); ?></strong></p>
        <p><?php
            _e('You can exclude all objects that do not match any category by using the parameter ' .
                '<code>skip_empty</code> and the value <code>true</code>.', 'oes');
            ?>
        </p>
        <p><?php
            _e('The monads are aligned in a clockwise circle, starting at 12 o\'clock. To rotate the data ' .
                'clockwise you can specify the parameter <code>rotate</code>. Valid parameters are float values ' .
                'between 0 (0°) and 1 (360°).', 'oes');
            ?>
        </p>
    </div>
    <div>
        <h2><?php _e('Remarks', 'oes'); ?></h2>
        <p><?php
            _e('If you do not ' .
                'use the OES theme you will probably have to customize the css styling to get a better presentation.',
                'oes');
            ?>
        </p>
    </div>
</div>