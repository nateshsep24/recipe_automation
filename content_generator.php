<?php
/**
 * Plugin Name: Dish Content Creator
 * Description: A plugin to generate detailed content for dish names using ChatGPT API, save raw output, and automatically create a new post in WordPress with tags.
 * Version: 1.4
 * Author: Natesh Kumar
 */
 
// Schedule events upon plugin activation
register_activation_hook(__FILE__, 'schedule_dish_content_creation_events');

function schedule_dish_content_creation_events() {
    if (!wp_next_scheduled('generate_dish_content_non_veg_event')) {
        wp_schedule_event(time(), 'daily', 'generate_dish_content_non_veg_event');
    }
    if (!wp_next_scheduled('generate_dish_content_veg_event')) {
        wp_schedule_event(strtotime('+8 hours'), 'daily', 'generate_dish_content_veg_event');
    }
    if (!wp_next_scheduled('generate_dish_content_vegan_event')) {
        wp_schedule_event(strtotime('+16 hours'), 'daily', 'generate_dish_content_vegan_event');
    }
}

// Clear events upon deactivation
register_deactivation_hook(__FILE__, 'clear_dish_content_creation_events');

function clear_dish_content_creation_events() {
    wp_clear_scheduled_hook('generate_dish_content_non_veg_event');
    wp_clear_scheduled_hook('generate_dish_content_veg_event');
    wp_clear_scheduled_hook('generate_dish_content_vegan_event');
}

// Action hooks to trigger content generation for each type
add_action('generate_dish_content_non_veg_event', function() { generate_dish_content_and_create_post('non_veg'); });
add_action('generate_dish_content_veg_event', function() { generate_dish_content_and_create_post('veg'); });
add_action('generate_dish_content_vegan_event', function() { generate_dish_content_and_create_post('vegan'); });

// Hook to add the generator to the WordPress admin menu
add_action('admin_menu', 'dish_content_creator_menu');

// Register the plugin settings page
function dish_content_creator_menu() {
    add_menu_page(
        'Dish Content Creator', // Page title
        'Dish Content Creator', // Menu title
        'manage_options',       // Capability
        'dish-content-creator', // Menu slug
        'dish_content_creator_page' // Function to display page content
    );
}

// Display the plugin settings page
// Display the plugin settings page
function dish_content_creator_page() {
    ?>
    <div class="wrap">
        <h1>Dish Content Creator</h1>
        <p>Click the button below to generate content for all dish types and create new posts:</p>
        <form method="post">
            <input type="submit" name="generate_dish_content" class="button button-primary" value="Generate Dish Content and Create Posts for Testing">
        </form>
        <?php
        if (isset($_POST['generate_dish_content'])) {
            $dish_types = ['non_veg', 'veg', 'vegan'];
            foreach ($dish_types as $dish_type) {
                $post_id = generate_dish_content_and_create_post($dish_type); // Pass each dish type to the function
                if ($post_id) {
                    echo "<h2>New post created successfully for $dish_type! Post ID: $post_id</h2>";
                } else {
                    echo "<h2>Error creating post for $dish_type. Please try again later.</h2>";
                }
            }
        }
        ?>
    </div>
    <?php
}

// Function to generate content and create a new post
function generate_dish_content_and_create_post($dish_type) {
    $api_key = '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'; // Replace with your actual API key

    // Call the relevant Dish Name Generator function
    $dish_info = [];
    if ($dish_type === 'non_veg') {
        $dish_info = generate_unique_non_veg_dish_name_with_chatgpt();
    } elseif ($dish_type === 'veg') {
        $dish_info = generate_unique_vegetarian_dish_name_with_chatgpt();
    } elseif ($dish_type === 'vegan') {
        $dish_info = generate_unique_vegan_dish_name_with_chatgpt();
    }

    if (empty($dish_info['dish_name']) || empty($dish_info['category_slug'])) {
        error_log("Dish Name or Category missing for $dish_type");
        return false;
    }

    $dish_name = $dish_info['dish_name'];
    $category_slug = $dish_info['category_slug'];
    $tags = $dish_info['tags']; // Tags received from the generator

    // Prepare the prompt to generate content for the dish
    $prompt = "Generate detailed content for the following dish: $dish_name. The content should include:\n\n" .
              "- A short introduction or backstory about the dish. $dish_name should be added first in the introduction.\n" .
              "- **Ingredients**: List all necessary ingredients.\n" .
              "- **Instructions**: Step-by-step cooking instructions.\n" .
              "- **Estimated Cooking/Preparation Time**: Estimate time required for preparation and cooking.\n" .
              "- **Pairing Suggestions**: Suggest complementary dishes or beverages.\n" .
              "- **Nutrition Information (per serving)**: Nutrition values in a table (including calories, protein, carbs, fats) with 2 columns Nutrient and Amount (per serving).\n" .
              "- **Cooking Tips & Variations**: Provide tips and possible variations.\n" .
              "- **Storage and Leftover Tips**: Suggest ways to store and reuse leftovers.\n" .
              "- **Common Allergens & Substitutes**: Identify common allergens and suggest substitutes.\n\n" .
              "Format the entire response in HTML with proper headings (H1, H2, H3), lists, and a table for nutrition values.";

    // Call ChatGPT API
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array('role' => 'system', 'content' => 'You are a recipe creator.'),
                array('role' => 'user', 'content' => $prompt)
            ),
            'max_tokens' => 1000
        )),
        'timeout' => 60
    ));

    // Error handling for API response
    if (is_wp_error($response)) {
        error_log('ChatGPT API request failed: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['choices'][0]['message']['content'])) {
        $raw_content = trim($data['choices'][0]['message']['content']);

        // Clean up the content
        $cleaned_data = clean_generated_content($raw_content, $dish_name);
        $post_title = $cleaned_data['title'];
        $post_content = $cleaned_data['content'];

        // Get the category ID
        $category_id = get_cat_ID($category_slug);

        // Create the post
        $post_id = wp_insert_post(array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => 'publish',
            'post_category' => array($category_id)
        ));

        // Add tags to the post
        if ($post_id) {
            wp_set_post_tags($post_id, $tags);
            return $post_id;
        } else {
            error_log('Error creating the post in WordPress.');
            return false;
        }
    } else {
        error_log('Error in ChatGPT API response: ' . $body);
        return false;
    }
}

// Function to clean up generated content
function clean_generated_content($content, $dish_name) {
    // Remove HTML document structure tags
    $content = preg_replace('/<!DOCTYPE html>|<html>|<\/html>|<head>.*?<\/head>|<body>|<\/body>/is', '', $content);

    // Extract and remove the <title> tag while saving the title text for later use
    preg_match('/<title>(.*?)<\/title>/is', $content, $matches);
    $title = isset($matches[1]) ? trim($matches[1]) : $dish_name; // Default to dish name if title is not found
    $content = preg_replace('/<title>.*?<\/title>/is', '', $content);

    // Remove any code block markers (```html and ```).
    $content = str_replace(['```html', '```'], '', $content);

    // Remove the first <h1> tag as we already have the title
    $content = preg_replace('/<h1[^>]*>.*?<\/h1>/i', '', $content, 1);

    // Remove any unwanted characters like curly quotes
    $content = str_replace(['“', '”', '‘', '’', '"'], '', $content);

    // Remove empty <p> tags
    $content = preg_replace('/<p>\s*<\/p>/', '', $content);

    // Ensure single spacing around headings
    $content = preg_replace('/(<\/h[2-4]>)/i', "$1\n", $content);
    $content = preg_replace('/(<h[2-4]>)/i', "\n$1", $content);

    // Return cleaned content and extracted title
    return ['title' => $title, 'content' => trim($content)];
}

?>
