<?php
/**
 * Plugin Name: Vegetarian Dish Name Generator
 * Description: A plugin to generate creative vegetarian dish names using ChatGPT API, with cuisine-specific ingredients categorized by frequency.
 * Version: 1.1
 * Author: Natesh Kumar
 */

// Hook to add the generator to the WordPress admin menu
add_action('admin_menu', 'vegetarian_dish_name_generator_menu');

// Register the plugin settings page
function vegetarian_dish_name_generator_menu() {
    add_menu_page(
        'Vegetarian Dish Name Generator', // Page title
        'Vegetarian Dish Generator',      // Menu title
        'manage_options',                 // Capability
        'vegetarian-dish-name-generator', // Menu slug
        'vegetarian_dish_name_generator_page' // Function to display page content
    );
}

// Display the plugin settings page
function vegetarian_dish_name_generator_page() {
    ?>
    <div class="wrap">
        <h1>Vegetarian Dish Name Generator</h1>
        <p>Click the button below to generate a creative vegetarian dish name using ChatGPT and return it with category:</p>
        <form method="post">
            <input type="submit" name="generate_dish_name" class="button button-primary" value="Generate Vegetarian Dish Name">
        </form>
        <?php
        if (isset($_POST['generate_dish_name'])) {
            $dish_info = generate_unique_vegetarian_dish_name_with_chatgpt();
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

// Function to generate a unique vegetarian dish name with weighted ingredient selection
function generate_unique_vegetarian_dish_name_with_chatgpt() {
    $api_key = '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';

    // Vegetarian cuisine-to-ingredient mapping with frequency categorization
$cuisine_ingredients = array(
    "Mexican" => [
        "Frequent" => ["Corn", "Beans", "Cheese (Queso Fresco, Cotija)", "Tortillas", "Rice", "Cilantro"],
        "Common" => ["Avocados", "Chilies"],
        "Rare" => []
    ],
    "Middle Eastern" => [
        "Frequent" => ["Chickpeas", "Lentils", "Eggplant", "Olives", "Yogurt"],
        "Common" => ["Pita Bread", "Feta Cheese", "Tahini", "Parsley", "Mint", "Spinach"],
        "Rare" => []
    ],
    "Indian" => [
        "Frequent" => ["Lentils", "Chickpeas", "Paneer", "Spinach", "Potatoes", "Rice"],
        "Common" => ["Yogurt", "Coconut", "Ghee"],
        "Rare" => ["Cauliflower"]
    ],
    "Moroccan" => [
        "Frequent" => ["Couscous", "Chickpeas", "Lentils", "Eggplant", "Zucchini", "Olives"],
        "Common" => ["Mint", "Cinnamon"],
        "Rare" => ["Raisins", "Almonds"]
    ],
    "Turkish" => [
        "Frequent" => ["Eggplant", "Lentils", "Yogurt", "Olives", "Bulgur", "Mint", "Parsley"],
        "Common" => ["Feta Cheese"],
        "Rare" => ["Spinach"]
    ],
    "Filipino" => [
        "Frequent" => ["Rice", "Coconut Milk", "Eggplant"],
        "Common" => ["Sweet Potatoes", "Spinach", "Mung Beans"],
        "Rare" => ["Okra", "Bitter Melon", "Taro", "Bananas"]
    ],
    "German" => [
        "Frequent" => ["Potatoes", "Cabbage", "Sausage", "Bread"],
        "Common" => ["Sauerkraut", "Beets", "Mushrooms"],
        "Rare" => ["Pickles", "Cheese (Quark, Emmental)"]
    ],
    "Mediterranean" => [
        "Frequent" => ["Olives", "Feta Cheese", "Yogurt", "Spinach", "Lentils", "Chickpeas"],
        "Common" => ["Eggplant", "Zucchini", "Parsley"],
        "Rare" => ["Mint"]
    ],
    "Russian" => [
        "Frequent" => ["Potatoes", "Cabbage", "Sour Cream"],
        "Common" => ["Beets", "Mushrooms", "Pickles"],
        "Rare" => ["Dill"]
    ],
    "French" => [
        "Frequent" => ["Potatoes", "Cheese (Brie, Camembert, Roquefort)", "Butter", "Eggs", "Cream"],
        "Common" => ["Mushrooms", "Leeks", "Herbs (Thyme, Tarragon)", "Spinach"],
        "Rare" => []
    ],
    "Vietnamese" => [
        "Frequent" => ["Rice", "Rice Noodles", "Tofu", "Cilantro", "Basil", "Mint", "Peanuts"],
        "Common" => ["Coconut Milk", "Mushrooms"],
        "Rare" => []
    ],
    "Brazilian" => [
        "Frequent" => ["Black Beans", "Rice", "Cassava"],
        "Common" => ["Sweet Potatoes", "Coconut Milk"],
        "Rare" => ["Plantains", "Palm Hearts", "Okra", "Cheese (Minas)"]
    ],
    "Argentinian" => [
        "Frequent" => ["Corn", "Potatoes", "Rice"],
        "Common" => ["Pumpkin", "Lentils"],
        "Rare" => ["Cheese", "Squash", "Peppers"]
    ],
    "Japanese" => [
        "Frequent" => ["Rice", "Tofu", "Soybeans", "Miso", "Edamame"],
        "Common" => ["Mushrooms (Shiitake, Enoki)", "Sweet Potatoes", "Green Tea"],
        "Rare" => ["Seaweed", "Lotus Root"]
    ],
    "Spanish" => [
        "Frequent" => ["Peppers", "Rice", "Chickpeas", "Potatoes"],
        "Common" => ["Eggplant", "Olives", "Cheese (Manchego)"],
        "Rare" => ["Paprika"]
    ],
    "Korean" => [
        "Frequent" => ["Kimchi", "Rice", "Tofu", "Soybeans"],
        "Common" => ["Mushrooms", "Seaweed", "Sesame Seeds"],
        "Rare" => ["Gochujang"]
    ],
    "African (various regional)" => [
        "Frequent" => ["Cassava", "Yams", "Plantains", "Okra", "Lentils", "Rice"],
        "Common" => ["Peanuts", "Chickpeas", "Sweet Potatoes"],
        "Rare" => ["Couscous", "Collard Greens", "Millet", "Sorghum"]
    ],
    "Peruvian" => [
        "Frequent" => ["Corn", "Potatoes", "Quinoa"],
        "Common" => ["Beans", "Sweet Potatoes", "Avocados"],
        "Rare" => ["Cilantro", "Peppers"]
    ],
    "Greek" => [
        "Frequent" => ["Feta Cheese", "Yogurt", "Olives", "Zucchini", "Spinach", "Potatoes"],
        "Common" => ["Eggplant", "Oregano"],
        "Rare" => []
    ],
    "Caribbean" => [
        "Frequent" => ["Plantains", "Rice", "Beans"],
        "Common" => ["Coconut", "Sweet Potatoes", "Cassava"],
        "Rare" => ["Avocados", "Peppers", "Okra", "Callaloo"]
    ],
    "Lebanese" => [
        "Frequent" => ["Chickpeas", "Lentils", "Yogurt", "Feta Cheese"],
        "Common" => ["Eggplant", "Parsley", "Mint", "Tahini"],
        "Rare" => ["Fava Beans", "Pita Bread"]
    ],
    "Thai" => [
        "Frequent" => ["Rice", "Rice Noodles", "Tofu", "Coconut Milk"],
        "Common" => ["Chilies", "Lemongrass", "Basil", "Mint", "Cilantro", "Peanuts", "Mushrooms"],
        "Rare" => ["Bamboo Shoots"]
    ],
    "Chinese" => [
        "Frequent" => ["Tofu", "Rice", "Rice Noodles", "Soybeans"],
        "Common" => ["Bok Choy", "Mushrooms", "Ginger", "Peanuts", "Spinach"],
        "Rare" => ["Snow Peas", "Lotus Root"]
    ],
    "American" => [
        "Frequent" => ["Potatoes", "Corn", "Beans"],
        "Common" => ["Cheese", "Bread", "Pumpkin", "Sweet Potatoes"],
        "Rare" => ["Mushrooms"]
    ],
    "Ethiopian" => [
        "Frequent" => ["Lentils", "Chickpeas", "Cabbage"],
        "Common" => ["Spinach", "Injera"],
        "Rare" => ["Berbere Spices", "Split Peas"]
    ],
    "Italian" => [
        "Frequent" => ["Olive Oil", "Basil", "Pasta", "Cheese (Mozzarella, Parmesan, Ricotta)"],
        "Common" => ["Mushrooms", "Eggplant", "Zucchini", "Polenta"],
        "Rare" => ["Artichokes", "Capers", "Arugula"]
    ]
);

    // Styles applicable for vegetarian dishes
    $styles = array('Grilled', 'Baked', 'Roasted', 'Spicy', 'Glazed', 'Braised', 'Marinated', 'Steamed');

    // Step 1: Select cuisine randomly
    $cuisines = array_keys($cuisine_ingredients);
    $random_cuisine = $cuisines[array_rand($cuisines)];
    $ingredients = $cuisine_ingredients[$random_cuisine];

    // Step 2: Weighted ingredient selection based on frequency
    $random_ingredient = get_weighted_random_ingredient($ingredients);

    // Step 3: Randomly pick a cooking style
    $random_style = $styles[array_rand($styles)];

    // Step 4: Generate the prompt
    $prompt = "Generate a creative and appetizing vegetarian dish name based on the following: $random_style $random_cuisine $random_ingredient.";

    // Loop to ensure uniqueness
    $max_attempts = 5;
    $attempt = 0;
    $unique_name_found = false;
    $dish_name = '';

    while ($attempt < $max_attempts && !$unique_name_found) {
        $attempt++;
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array('role' => 'system', 'content' => 'You are a chef that specializes in creating unique and creative vegetarian dish names.'),
                    array('role' => 'user', 'content' => $prompt)
                ),
                'max_tokens' => 50
            )),
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            error_log('ChatGPT API request failed: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['choices'][0]['message']['content'])) {
            $dish_name = trim($data['choices'][0]['message']['content']);
            $category_slug = map_cuisine_to_category_veg($random_cuisine);
            $unique_name_found = true;
            
            return [
                'dish_name' => $dish_name,
                'category_slug' => $category_slug,
                'tags' => [$random_cuisine, $random_ingredient, $random_style, 'veg']
            ];
        } else {
            error_log('Error in ChatGPT API response: ' . $body);
            return false;
        }
    }

    return $unique_name_found ? $dish_name : false;
}

// Function for weighted ingredient selection based on frequency
function get_weighted_random_ingredient($ingredients) {
    $ingredient_pool = [];

    foreach ($ingredients as $frequency => $ingredient_list) {
        $weight = match($frequency) {
            "Frequent" => 0.6,
            "Common" => 0.3,
            "Rare" => 0.1,
            default => 0.1,
        };
        
        for ($i = 0; $i < $weight * 10; $i++) { // Multiply to create a larger pool for selection
            $ingredient_pool = array_merge($ingredient_pool, $ingredient_list);
        }
    }

    return $ingredient_pool[array_rand($ingredient_pool)];
}

// Function to map cuisine to the corresponding category
function map_cuisine_to_category_veg($cuisine) {
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

    return isset($cuisine_category_map[$cuisine]) ? $cuisine_category_map[$cuisine] : 'vegetarian-recipes';
}
?>
