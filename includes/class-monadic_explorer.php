<?php

namespace OES\Monadic;

use WP_Post;
use WP_Term;
use function OES\Versioning\get_current_version_id;
use function OES\Versioning\get_parent_id;

if (!defined('ABSPATH')) exit;

/**
 * Class Explorer
 *
 * Renders and prepares data for a Monadic Explorer UI component in WordPress.
 * It processes custom post types and taxonomies, applies categorization logic,
 * and outputs a searchable, interactive data map.
 *
 * @package OES\Monadic
 */
class Explorer
{
    /**
     * @var string Active language slug.
     */
    private string $language = '';

    /**
     * @var array<string, array> Category metadata keyed by slug.
     */
    private array $categories = [];

    /**
     * @var array<string, array> Prepared data for rendering.
     */
    private array $data = [];

    /**
     * @var int Counter for assigning unique IDs to categories.
     */
    private int $consecutive = 0;

    /**
     * @var int Category ID for empty/uncategorised items.
     */
    private int $empty_category_key;

    /**
     * @var bool Whether to skip items with no category.
     */
    private bool $skip_empty;

    /**
     * @var array Visualization options (e.g. title fields, center texts, rotate).
     */
    private array $options = [];

    /**
     * @var array Object configuration (custom post types/taxonomies).
     */
    private array $objects = [];

    /**
     * Explorer constructor.
     *
     * @param array $args Configuration arguments.
     */
    public function __construct(array $args)
    {
        $this->set_language($args['language'] ?? '');
        $this->set_options($args);
        $this->set_objects($args);
        $this->set_skip_empty($args['skip_empty'] ?? true);
        $this->set_categories($args);
        $this->validate_categories();
    }

    /**
     * Render the final HTML and JavaScript to embed the explorer.
     *
     * @return string
     */
    public function render(): string
    {
        $this->prepare_data();
        $cleanData = array_filter($this->data, fn($d) => isset($d['type']));

        return '
        <div id="oes-me-container">
            <div class="oes-me">
                <nav id="oes-me-legend"><ul class="oes-vertical-list"></ul></nav>
                <div id="oes-me-center">
                    <input type="search" name="search" value="" id="oes-me-search" placeholder="search">
                </div>
                <div id="oes-me-canvas"></div>
            </div>
        </div>
        <script type="text/javascript">
            oesMonadicNodeTypes = ' . json_encode(array_values($this->categories)) . ';
            oesMonadicOptions = ' . json_encode($this->options) . ';
            oesMonadicData = ' . json_encode(array_values($cleanData), JSON_INVALID_UTF8_SUBSTITUTE) . ';
        </script>';
    }

    /**
     * Set the language context.
     *
     * @param string $language
     * @return void
     */
    protected function set_language(string $language): void
    {
        if (empty($language)) {
            global $oes_language;
            $language = $oes_language ?? 'language0';
        }
        $this->language = $language;
    }

    /**
     * Populate configuration options.
     *
     * @param array $args
     * @return void
     */
    protected function set_options(array $args): void
    {
        foreach ($args as $key => $value) {
            if ($key === 'rotate') {
                $this->options['rotate'] = (float)$value;
            } elseif (
                in_array($key, ['length', 'center_order'], true)
                || str_ends_with($key, '_title_text')
                || str_ends_with($key, '_center_text')
            ) {
                $this->options[$key] = $value;
            }
        }
    }

    /**
     * Define which post types and taxonomies to include.
     *
     * @param array $args
     * @return void
     */
    protected function set_objects(array $args): void
    {
        global $oes;
        $j = 1;

        while (isset($args['object' . $j])) {
            $objectType = $args['object' . $j];

            if (isset($oes->post_types[$objectType]) || isset($oes->taxonomies[$objectType])) {
                $this->objects[$objectType] = [
                    'is_taxonomy' => isset($oes->taxonomies[$objectType]),
                    'categorise' => $args['categorise' . $j] ?? false,
                    'fields' => $args['fields' . $j] ?? '',
                    'parent_post_type' => $oes->post_types[$objectType]['parent'] ?? false,
                    'version_post_type' => $oes->post_types[$objectType]['version'] ?? false,
                    'language_field' => $oes->post_types[$objectType]['language'] ?? false,
                ];
            }

            $j++;
        }
    }

    /**
     * Set flag for skipping items with no category.
     *
     * @param mixed $value
     * @return void
     */
    protected function set_skip_empty($value): void
    {
        $this->skip_empty = is_string($value) ? $value === 'true' : (bool)$value;
    }

    /**
     * Define categories via input args.
     *
     * @param array $args
     * @return void
     */
    protected function set_categories(array $args): void
    {
        $i = 1;
        while (isset($args['cat' . $i])) {
            $values = explode(';', $args['cat' . $i++]);

            if (count($values) > 1 && !isset($this->categories[$values[0]])) {
                $this->categories[$values[0]] = [
                    'slug' => $values[0],
                    'name' => $values[1],
                    'names' => $values[2] ?? $values[1],
                    'color' => $values[3] ?? '#111111',
                    'consecutive' => $this->consecutive++,
                ];
            }
        }
    }

    /**
     * Ensure "none" category exists unless skipped.
     *
     * @return void
     */
    protected function validate_categories(): void
    {
        if (!isset($this->categories['none']) && !$this->skip_empty) {
            $this->categories['none'] = [
                'slug' => 'none',
                'name' => __('Missing Category', 'oes'),
                'names' => __('Missing Categories', 'oes'),
                'color' => '#111111',
                'consecutive' => $this->consecutive,
            ];
        } elseif ($this->skip_empty) {
            unset($this->categories['none']);
        }

        $this->empty_category_key = $this->categories['none']['consecutive'] ?? $this->consecutive;
    }

    /**
     * Process and build the full data structure for rendering.
     *
     * @return void
     */
    protected function prepare_data(): void
    {
        foreach ($this->objects as $objectKey => $objectData) {
            if ($objectData['is_taxonomy']) {
                // @oesDevelopment: Future taxonomy handling.
            }
            else {

                $posts = get_posts([
                    'post_type' => $objectKey,
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                ]);

                foreach ($posts as $objectPost) {
                    if ($this->is_language_match($objectPost->ID, $objectData['language_field'])) {
                        $this->handle_post($objectKey, $objectPost);
                    }
                }
            }
        }
    }

    /**
     * Check if the post matches the required language.
     *
     * @param int $postID
     * @param string $languageField
     * @return bool
     */
    protected function is_language_match($postID, $languageField = ''): bool
    {
        if ($languageField) {
            $postLanguage = oes_get_field($languageField, $postID);
            return $postLanguage === $this->language;
        }
        return true;
    }

    /**
     * Process a post into the data structure.
     *
     * @param string $postType
     * @param WP_Post $objectPost
     * @return void
     */
    protected function handle_post(string $postType, $objectPost): void
    {
        $config = $this->objects[$postType];

        $parentID = $config['parent_post_type'] ? get_parent_id($objectPost->ID) : false;
        $versionID = $config['version_post_type'] ? get_current_version_id($objectPost->ID) : false;
        $fieldArray = $config['fields'] ?? '';
        $categorised = $config['categorise'] ?? false;

        $linkedPostID = $parentID ?: $objectPost->ID;
        $displayedPostID = $versionID ?: $objectPost->ID;

        $nodeKey = $categorised
            ? $this->determine_node_key($objectPost->ID, $parentID, $versionID, $categorised)
            : ($config['parent_post_type'] ? $postType : 'none');
        
        $nodeID = $this->categories[$nodeKey]['consecutive'] ?? ($this->skip_empty ? false : $this->empty_category_key);

        if (!isset($this->data['p_' . $linkedPostID]) && $nodeID !== false) {
            $this->data['p_' . $linkedPostID] = [
                'id' => 'p_' . $linkedPostID,
                'type' => $nodeID,
                'title' => $this->get_text($displayedPostID, '', $postType),
                'text' => $this->get_text($displayedPostID, '', $postType, '_center_text'),
                'url' => get_permalink($displayedPostID),
                'links' => [],
            ];
        }

        foreach (explode(';', $fieldArray) as $fieldKey) {
            $this->handle_field_links($fieldKey, $objectPost->ID, $linkedPostID, $parentID, $versionID);
        }
    }

    /**
     * Determines the category key for a given post based on a categorization rule.
     *
     * @param int         $postID        The ID of the current post.
     * @param int|null    $parentID      The ID of the parent post, if relevant.
     * @param int|null    $versionID     The ID of the version post, if relevant.
     * @param string      $categorisedBy The categorization rule (e.g., 'taxonomy__category', 'post_type', 'parent_taxonomy__x').
     *
     * @return string Category key (either 'taxonomy__id', post type, or 'none').
     */
    protected function determine_node_key($postID, $parentID, $versionID, string $categorisedBy): string
    {
        $categoryPostID = $postID;

        if ($parentID && str_starts_with($categorisedBy, 'parent_')) {
            $categorisedBy = substr($categorisedBy, 7);
            $categoryPostID = $parentID;
        }
        elseif($versionID && str_starts_with($categorisedBy, 'version_')){
            $categorisedBy = substr($categorisedBy, 8);
            $categoryPostID = $versionID;
        }

        if (str_starts_with($categorisedBy, 'taxonomy__')) {
            $taxonomyKey = substr($categorisedBy, 10);
            $terms = get_the_terms($categoryPostID, $taxonomyKey);
            if (!empty($terms) && !is_wp_error($terms)) {
                return $taxonomyKey . '_' . $terms[0]->term_id;
            }
        }

        global $oes;
        if (isset($oes->post_types[$categorisedBy])) {
            return $categorisedBy;
        }

        return 'none';
    }

    /**
     * Handles the extraction of links for a given post field or taxonomy relationship.
     *
     * @param string   $fieldKey      Field key (e.g., 'taxonomy__category', 'field__related_posts', or prefixed with 'parent_').
     * @param int      $postID        The current post ID.
     * @param int      $linkedPostID  The ID used as the source node in the graph.
     * @param int|null $parentID      The ID of the parent post, if relevant.
     * @param int|null $versionID     The ID of the version post, if relevant.
     *
     * @return void
     */
    protected function handle_field_links(string $fieldKey, $postID, $linkedPostID, $parentID, $versionID): void
    {
        if ($parentID && str_starts_with($fieldKey, 'parent_')) {
            $fieldKey = substr($fieldKey, 7);
            $postID = $parentID;
        }
        elseif ($versionID && str_starts_with($fieldKey, 'version_')) {
            $fieldKey = substr($fieldKey, 8);
            $postID = $versionID;
        }

        if (str_starts_with($fieldKey, 'taxonomy__')) {
            $this->handle_taxonomy($fieldKey, $postID, $linkedPostID);
        } elseif (str_starts_with($fieldKey, 'field__')) {
            $this->handle_field(substr($fieldKey, 7), $postID, $linkedPostID);
        }
    }

    /**
     * Handles taxonomy relationship fields by linking all terms of a taxonomy.
     *
     * @param string $fieldKey      The full taxonomy field key (e.g. 'taxonomy__category').
     * @param int    $postID        The post ID to fetch taxonomy terms from.
     * @param int    $linkedPostID  The post ID to which the terms are linked.
     *
     * @return void
     */
    protected function handle_taxonomy(string $fieldKey, $postID, $linkedPostID): void
    {
        $taxonomyKey = substr($fieldKey, 10);
        $terms = get_the_terms($postID, $taxonomyKey);

        if (empty($terms) || is_wp_error($terms)) {
            return;
        }

        foreach ($terms as $term) {
            $this->add_linked_term($term, $linkedPostID);
        }
    }

    /**
     * Handles custom fields (ACF or similar), including post and taxonomy relationships.
     *
     * @param string $fieldKey      Field name/key as defined in ACF.
     * @param int    $postID        The post ID from which the field is retrieved.
     * @param int    $linkedPostID  The source node to link other objects to.
     *
     * @return void
     */
    protected function handle_field(string $fieldKey, $postID, $linkedPostID): void
    {
        $fieldObject = oes_get_field_object($fieldKey);
        if (!$fieldObject || !isset($fieldObject['type'])) {
            return;
        }

        $value = oes_get_field($fieldKey, $postID);

        switch ($fieldObject['type']) {
            case 'relationship':
            case 'post_object':
                $posts = is_array($value) ? $value : ($value ? [$value] : []);
                foreach ($posts as $post) {
                    if (!($post instanceof WP_Post)) {
                        $post = get_post($post);
                    }
                    if ($post instanceof WP_Post) {
                        $this->add_linked_post($post, $linkedPostID);
                    }
                }
                break;

            case 'taxonomy':
                $terms = is_array($value) ? $value : ($value ? [$value] : []);
                foreach ($terms as $term) {
                    if (!($term instanceof WP_Term)) {
                        $term = get_term($term);
                    }
                    if ($term instanceof WP_Term) {
                        $this->add_linked_term($term, $linkedPostID);
                    }
                }
                break;
        }
    }

    /**
     * Adds a linked post node to the graph structure and establishes a connection.
     *
     * @param WP_Post|int $post The post object or ID to link.
     * @param int|string   $linkedPostID The source post ID to which this post is linked.
     *
     * @return void
     */
    protected function add_linked_post($post, $linkedPostID): void
    {
        if (!($post instanceof WP_Post)) {
            $post = get_post($post);
        }

        if (!$post instanceof WP_Post) {
            return;
        }

        $typeID = $this->categories[$post->post_type]['consecutive']
            ?? ($this->skip_empty ? false : $this->empty_category_key);

        if ($typeID === false) {
            return;
        }

        $postID = 'p_' . $post->ID;
        $this->add_link($linkedPostID, $postID);

        if (!isset($this->data[$postID])) {
            $this->data[$postID] = [
                'id'    => $postID,
                'type'  => $typeID,
                'title' => $this->get_text($post->ID, '', $post->post_type),
                'text'  => $this->get_text($post->ID, '', $post->post_type, '_center_text'),
                'url'   => get_permalink($post->ID),
                'links' => ['p_' . $linkedPostID],
            ];
        } elseif (!in_array('p_' . $linkedPostID, $this->data[$postID]['links'])) {
            $this->data[$postID]['links'][] = 'p_' . $linkedPostID;
        }
    }

    /**
     * Adds a linked taxonomy term node to the graph structure and establishes a connection.
     *
     * @param WP_Term|int $term The term object or ID to link.
     * @param int|string   $linkedPostID The source post ID to which this term is linked.
     *
     * @return void
     */
    protected function add_linked_term($term, $linkedPostID): void
    {
        if (!($term instanceof WP_Term)) {
            $term = get_term($term);
        }

        if (!$term instanceof WP_Term) {
            return;
        }

        $taxonomyKey = $term->taxonomy;
        $typeID = $this->categories[$taxonomyKey]['consecutive']
            ?? ($this->skip_empty ? false : $this->empty_category_key);

        if ($typeID === false) {
            return;
        }

        $termID = 't_' . $term->term_id;
        $this->add_link($linkedPostID, $termID);

        if (!isset($this->data[$termID])) {
            $this->data[$termID] = [
                'id'    => $termID,
                'type'  => $typeID,
                'title' => $this->get_text($term->term_id, $taxonomyKey, $taxonomyKey),
                'text'  => $this->get_text($term->term_id, $taxonomyKey, $taxonomyKey, '_center_text'),
                'url'   => get_term_link($term->term_id),
                'links' => ['p_' . $linkedPostID],
            ];
        } elseif (!in_array('p_' . $linkedPostID, $this->data[$termID]['links'])) {
            $this->data[$termID]['links'][] = 'p_' . $linkedPostID;
        }
    }

    /**
     * Adds a link/connection between two node IDs in the data structure.
     *
     * @param string $fromID Source node ID (usually prefixed with 'p_').
     * @param string $toID   Target node ID (usually prefixed with 'p_' or 't_').
     *
     * @return void
     */
    protected function add_link(string $fromID, string $toID): void
    {
        $key = 'p_' . $fromID;
        if (!in_array($toID, $this->data[$key]['links'] ?? [])) {
            $this->data[$key]['links'][] = $toID;
        }
    }

    /**
     * Gets the title or description text for a post or term object based on configuration.
     *
     * @param int         $objectID    The object ID (post or term).
     * @param string      $taxonomy    The taxonomy slug (empty for posts).
     * @param string      $objectType  The post type or taxonomy key.
     * @param string      $optionPart  Either '_title_text' or '_center_text'.
     *
     * @return string The displayable text.
     */
    protected function get_text($objectID, string $taxonomy = '', string $objectType = '', string $optionPart = '_title_text'): string
    {
        $option  = $this->options[$objectType . $optionPart] ?? ($optionPart === '_title_text' ? 'title' : 'default');
        $default = ($optionPart === '_title_text') ? 'title' : 'default';

        $text = $this->calculate_get_text($objectID, $taxonomy, $option);

        if (empty($text) && $option !== $default) {
            $text = $this->calculate_get_text($objectID, $taxonomy, $default);
        }

        $length = $this->options['length'] ?? false;

        return ($length && is_int($length) && strlen($text) > $length)
            ? substr(strip_tags(strip_shortcodes($text)), 0, $length)
            : strip_shortcodes($text);
    }

    /**
     * Resolves the actual display text for an object using a configured strategy.
     *
     * @param int|string  $objectID   The object ID (post ID or term ID).
     * @param string      $taxonomy   The taxonomy slug if a term, empty for post.
     * @param string      $option     Display strategy ('none', 'title', 'default', function name, ACF field key, or 'string:').
     *
     * @return string The resolved text.
     */
    protected function calculate_get_text($objectID, string $taxonomy = '', string $option = 'default'): string
    {
        if ($option === 'none' || ($option === 'default' && !empty($taxonomy))) {
            return '';
        }

        if ($option === 'title') {
            return oes_get_display_title(empty($taxonomy) ? $objectID : get_term($objectID));
        }

        if ($option === 'default') {
            $post = get_post($objectID);
            return $post->post_content ?? '';
        }

        if (function_exists($option)) {
            return call_user_func($option, $objectID);
        }

        if (oes_get_field_object($option)) {
            $fieldKey = empty($taxonomy) ? $objectID : $taxonomy . '_' . $objectID;
            return oes_get_field_display_value($option, $fieldKey);
        }

        if (str_starts_with($option, 'string:')) {
            return substr($option, 7);
        }

        return '';
    }
}
