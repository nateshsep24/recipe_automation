<?php
/**
 * Plugin Name: Non-Veg Dish Name Generator
 * Description: A plugin to generate creative non-vegetarian dish names using ChatGPT API, assign appropriate categories based on cuisine, and return dish name and category.
 * Version: 1.2
 * Author: Natesh Kumar
 */

// Hook to add the generator to the WordPress admin menu
add_action('admin_menu', 'non_veg_dish_name_generator_menu');

// Register the plugin settings page
function non_veg_dish_name_generator_menu() {
    add_menu_page(
        'Non-Veg Dish Name Generator', // Page title
        'Non-Veg Dish Generator',      // Menu title
        'manage_options',              // Capability
        'non-veg-dish-name-generator', // Menu slug
        'non_veg_dish_name_generator_page' // Function to display page content
    );
}

// Display the plugin settings page
function non_veg_dish_name_generator_page() {
    ?>
    <div class="wrap">
        <h1>Non-Veg Dish Name Generator</h1>
        <p>Click the button below to generate a creative non-vegetarian dish name using ChatGPT and return it with category:</p>
        <form method="post">
            <input type="submit" name="generate_dish_name" class="button button-primary" value="Generate Non-Veg Dish Name">
        </form>
        <?php
        if (isset($_POST['generate_dish_name'])) {
            $dish_info = generate_unique_non_veg_dish_name_with_chatgpt();
            if ($dish_info && isset($dish_info['dish_name']) && isset($dish_info['category_slug'])) {
                echo "<h2>Generated Dish Name: {$dish_info['dish_name']}</h2>";
                echo "<p>Category: {$dish_info['category_slug']}</p>";
            } else {
                echo "<h2>Error generating dish name. Please try again later.</h2>";
            }
        }
        ?>
    </div>
    <?php
}

// Function to generate a unique non-veg dish name and return it with category slug
function generate_unique_non_veg_dish_name_with_chatgpt() {
    $api_key = 'API_KEY'; // Replace with your actual OpenAI API key

    // Cuisine ingredients classified by frequency
    $cuisine_ingredients_classified = array(
    "Mexican" => [
        "frequent" => ["Chicken", "Pork", "Beef", "Fish", "Shrimp"],
        "common" => ["Lamb", "Turkey"],
        "rare" => ["Grasshoppers", "Crickets", "Iguana"]
    ],
    "Middle Eastern" => [
        "frequent" => ["Lamb", "Chicken", "Beef", "Fish"],
        "common" => ["Shrimp", "Goat"],
        "rare" => ["Squab", "Duck"]
    ],
    "Indian" => [
        "frequent" => ["Chicken", "Lamb", "Goat", "Fish", "Shrimp"],
        "common" => ["Beef", "Pork", "Crab"],
        "rare" => ["Mutton", "Squid"]
    ],
    "Moroccan" => [
        "frequent" => ["Lamb", "Chicken", "Beef", "Fish"],
        "common" => ["Squid", "Goat"],
        "rare" => ["Camel", "Pigeon"]
    ],
    "Turkish" => [
        "frequent" => ["Lamb", "Chicken", "Beef", "Fish"],
        "common" => ["Doner kebab"],
        "rare" => ["Pigeon"]
    ],
    "Filipino" => [
        "frequent" => ["Pork", "Chicken", "Beef", "Fish", "Shrimp"],
        "common" => ["Crab", "Squid", "Goat"],
        "rare" => []
    ],
    "German" => [
        "frequent" => ["Pork", "Beef", "Chicken", "Sausage"],
        "common" => ["Rabbit", "Duck"],
        "rare" => ["Goose", "Veal"]
    ],
    "Mediterranean" => [
        "frequent" => ["Lamb", "Chicken", "Fish", "Beef", "Shrimp"],
        "common" => ["Squid", "Goat"],
        "rare" => []
    ],
    "Russian" => [
        "frequent" => ["Beef", "Chicken", "Fish", "Sausage"],
        "common" => ["Lamb", "Duck"],
        "rare" => ["Goose", "Caviar"]
    ],
    "French" => [
        "frequent" => ["Beef", "Chicken", "Pork", "Lamb"],
        "common" => ["Duck", "Goose", "Rabbit"],
        "rare" => ["Frog legs", "Pigeon", "Escargot", "Veal", "Quail", "Fish"]
    ],
    "Vietnamese" => [
        "frequent" => ["Pork", "Chicken", "Fish", "Shrimp"],
        "common" => ["Duck", "Crab"],
        "rare" => ["Frog", "Snails", "Snake"]
    ],
    "Brazilian" => [
        "frequent" => ["Beef", "Pork", "Chicken", "Fish", "Shrimp"],
        "common" => ["Goat"],
        "rare" => ["Turtle", "Alligator"]
    ],
    "Argentinian" => [
        "frequent" => ["Beef", "Lamb", "Pork", "Chicken"],
        "common" => ["Fish", "Goat"],
        "rare" => []
    ],
    "Japanese" => [
        "frequent" => ["Tuna", "Salmon", "Eel", "Mackerel", "Chicken"],
        "common" => ["Beef", "Pork", "Shrimp", "Octopus", "Crab"],
        "rare" => ["Uni (Sea urchin)", "Whale", "Fugu (Pufferfish)", "Horse"]
    ],
    "Spanish" => [
        "frequent" => ["Pork", "Chicken", "Beef", "Fish", "Shrimp"],
        "common" => ["Octopus", "Squid", "Chorizo", "Sardines"],
        "rare" => ["Rabbit", "Lamb"]
    ],
    "Korean" => [
        "frequent" => ["Beef", "Pork", "Chicken", "Shrimp"],
        "common" => ["Octopus", "Squid", "Crab"],
        "rare" => ["Duck", "Dog", "Abalone"]
    ],
    "African (various regional)" => [
        "frequent" => ["Goat", "Chicken", "Fish", "Beef", "Lamb"],
        "common" => ["Duck", "Pigeon"],
        "rare" => ["Camel"]
    ],
    "Peruvian" => [
        "frequent" => ["Chicken", "Fish", "Shrimp", "Beef"],
        "common" => ["Pork", "Lamb"],
        "rare" => ["Guinea pig", "Duck"]
    ],
    "Greek" => [
        "frequent" => ["Lamb", "Chicken", "Beef", "Fish", "Shrimp"],
        "common" => ["Squid", "Octopus", "Goat"],
        "rare" => []
    ],
    "Caribbean" => [
        "frequent" => ["Chicken", "Pork", "Fish", "Shrimp"],
        "common" => ["Goat", "Lobster", "Crab"],
        "rare" => ["Conch"]
    ],
    "Lebanese" => [
        "frequent" => ["Lamb", "Chicken", "Beef", "Fish"],
        "common" => ["Shrimp", "Goat"],
        "rare" => ["Squab"]
    ],
    "Thai" => [
        "frequent" => ["Chicken", "Pork", "Beef", "Fish", "Shrimp"],
        "common" => ["Crab", "Squid", "Duck"],
        "rare" => ["Frog", "Buffalo"]
    ],
    "Chinese" => [
        "frequent" => ["Pork", "Chicken", "Duck", "Beef", "Lamb"],
        "common" => ["Shrimp", "Crab", "Fish", "Squid", "Eel"],
        "rare" => ["Goose", "Snake", "Frog legs", "Turtle", "Pigeon", "Abalone", "Jellyfish"]
    ],
    "American" => [
        "frequent" => ["Chicken", "Beef", "Pork", "Turkey", "Fish"],
        "common" => ["Shrimp", "Lobster", "Crab", "Clams", "Catfish"],
        "rare" => ["Alligator", "Bison", "Venison", "Frog legs"]
    ],
    "Ethiopian" => [
        "frequent" => ["Chicken", "Beef", "Lamb", "Goat"],
        "common" => ["Fish"],
        "rare" => ["Mutton"]
    ],
    "Italian" => [
        "frequent" => ["Chicken", "Beef", "Pork"],
        "common" => ["Prosciutto", "Sausage", "Veal", "Lamb"],
        "rare" => ["Anchovies", "Tuna", "Bacon", "Swordfish", "Octopus", "Squid", "Sardines", "Rabbit", "Quail", "Pigeon"]
    ]
);

    // Function to randomly select ingredient based on frequency
    function select_ingredient($ingredients) {
        // Determine probability weights: frequent 60%, common 30%, rare 10%
        $category_choice = rand(1, 100);
        if ($category_choice <= 60 && !empty($ingredients['frequent'])) {
            return $ingredients['frequent'][array_rand($ingredients['frequent'])];
        } elseif ($category_choice <= 90 && !empty($ingredients['common'])) {
            return $ingredients['common'][array_rand($ingredients['common'])];
        } elseif (!empty($ingredients['rare'])) {
            return $ingredients['rare'][array_rand($ingredients['rare'])];
        }
        return $ingredients['frequent'][array_rand($ingredients['frequent'])]; // Fallback
    }

    // Select a random cuisine and ingredient
    $cuisines = array_keys($cuisine_ingredients_classified);
    $random_cuisine = $cuisines[array_rand($cuisines)];
    $random_ingredient = select_ingredient($cuisine_ingredients_classified[$random_cuisine]);

    // Styles applicable for non-veg dishes
    $styles = array('Grilled', 'Baked', 'Roasted', 'Spicy', 'Smoked', 'Glazed', 'Braised', 'Marinated');
    $random_style = $styles[array_rand($styles)];

    // Generate the prompt
    $prompt = "Generate a creative and appetizing non-vegetarian dish name based on the following: $random_style $random_cuisine $random_ingredient.";

 // Loop to ensure uniqueness
    $max_attempts = 5;
    $attempt = 0;
    $unique_name_found = false;
    $dish_name = '';

    while ($attempt < $max_attempts && !$unique_name_found) {
        // Increment attempt counter
        $attempt++;

        // Prepare the API request
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array(
                        'role' => 'system', 
                        'content' => 'You are a chef that specializes in creating unique and creative non-vegetarian dish names.'
                    ),
                    array(
                        'role' => 'user', 
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 50
            )),
            'timeout' => 60
	));}

    // Check if the API request was successful
    if (is_wp_error($response)) {
        error_log('ChatGPT API request failed: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Extract the dish name from the response
    if (isset($data['choices'][0]['message']['content'])) {
        $dish_name = trim($data['choices'][0]['message']['content']);
        $category_slug = map_cuisine_to_category($random_cuisine);

        return [
            'dish_name' => $dish_name,
            'category_slug' => $category_slug,
			'tags' => [$random_cuisine, $random_ingredient, $random_style, 'Non Veg']
        ];
    } else {
        error_log('Error in ChatGPT API response: ' . $body);
        return false;
    }
}

// Function to map cuisine to the corresponding category
function map_cuisine_to_category($cuisine) {
    $cuisine_category_map = array(
        'Mexican' => 'mexican-fusion',
        'Middle Eastern' => 'mediterranean-fusion',
        'Indian' => 'asian-fusion',
        'Moroccan' => 'mediterranean-fusion',
        'Turkish' => 'mediterranean-fusion',
        'Filipino' => 'asian-fusion',
        'German' => 'global-fusion',
        'Mediterranean' => 'mediterranean-fusion',
        'Russian' => 'global-fusion',
        'French' => 'global-fusion',
        'Vietnamese' => 'asian-fusion',
        'Brazilian' => 'global-fusion',
        'Argentinian' => 'global-fusion',
        'Japanese' => 'asian-fusion',
        'Spanish' => 'mediterranean-fusion',
        'Korean' => 'asian-fusion',
        'African (various regional)' => 'global-fusion',
        'Peruvian' => 'global-fusion',
        'Greek' => 'mediterranean-fusion',
        'Caribbean' => 'global-fusion',
        'Lebanese' => 'mediterranean-fusion',
        'Thai' => 'asian-fusion',
        'Chinese' => 'asian-fusion',
        'American' => 'global-fusion',
        'Ethiopian' => 'global-fusion',
        'Italian' => 'mediterranean-fusion'
    );

    return isset($cuisine_category_map[$cuisine]) ? $cuisine_category_map[$cuisine] : 'global-fusion';
}
?>
