<?php
/**
 * Plugin Name: Vegan Dish Name Generator
 * Description: A plugin to generate creative vegan dish names using ChatGPT API, with cuisine-specific ingredients.
 * Version: 1.1
 * Author: Natesh Kumar
 */

// Hook to add the generator to the WordPress admin menu
add_action('admin_menu', 'vegan_dish_name_generator_menu');

// Register the plugin settings page
function vegan_dish_name_generator_menu() {
    add_menu_page(
        'Vegan Dish Name Generator', // Page title
        'Vegan Dish Generator',      // Menu title
        'manage_options',            // Capability
        'vegan-dish-name-generator', // Menu slug
        'vegan_dish_name_generator_page' // Function to display page content
    );
}

// Display the plugin settings page
function vegan_dish_name_generator_page() {
    ?>
    <div class="wrap">
        <h1>Vegan Dish Name Generator</h1>
        <p>Click the button below to generate a creative vegan dish name using ChatGPT:</p>
        <form method="post">
            <input type="submit" name="generate_dish_name" class="button button-primary" value="Generate Vegan Dish Name">
        </form>
        <?php
        if (isset($_POST['generate_dish_name'])) {
            $dish_info = generate_unique_vegan_dish_name_with_chatgpt();
            if ($dish_info && isset($dish_info['dish_name']) && isset($dish_info['category_slug'])) {
                echo "<h2>Generated Dish Name: {$dish_info['dish_name']}</h2>";
                echo "<p>Category: {$dish_info['category_slug']}</p>";
                echo "<p>Tags: " . implode(', ', $dish_info['tags']) . "</p>";
            } else {
                echo "<h2>Error generating dish name. Please try again later.</h2>";
            }
        }
        ?>
    </div>
    <?php
}

// Function to generate a unique vegan dish name and return it with category slug
function generate_unique_vegan_dish_name_with_chatgpt() {
    $api_key = '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'; // Replace with your actual OpenAI API key

    // Define the cuisine ingredients categorized by frequency
    $cuisine_ingredients = array(
    "Mexican" => [
        "Frequent" => ["Corn", "Beans", "Tortillas", "Rice"],
        "Common" => ["Cilantro", "Avocados", "Chilies"],
        "Rare" => []
    ],
    "Middle Eastern" => [
        "Frequent" => ["Chickpeas", "Lentils", "Eggplant", "Olives"],
        "Common" => ["Pita Bread", "Tahini", "Spinach", "Parsley"],
        "Rare" => ["Mint"]
    ],
    "Indian" => [
        "Frequent" => ["Lentils", "Chickpeas", "Spinach", "Potatoes", "Rice"],
        "Common" => ["Coconut", "Cauliflower"],
        "Rare" => ["Curry Leaves"]
    ],
    "Moroccan" => [
        "Frequent" => ["Couscous", "Chickpeas", "Lentils", "Eggplant", "Olives"],
        "Common" => ["Mint", "Zucchini", "Raisins"],
        "Rare" => ["Almonds", "Cinnamon"]
    ],
    "Turkish" => [
        "Frequent" => ["Eggplant", "Lentils", "Olives"],
        "Common" => ["Bulgur", "Spinach", "Parsley"],
        "Rare" => ["Mint"]
    ],
    "Filipino" => [
        "Frequent" => ["Rice", "Sweet Potatoes", "Coconut Milk"],
        "Common" => ["Eggplant", "Okra", "Spinach", "Bananas"],
        "Rare" => ["Bitter Melon", "Taro", "Mung Beans"]
    ],
    "German" => [
        "Frequent" => ["Potatoes", "Cabbage", "Mushrooms"],
        "Common" => ["Sauerkraut", "Beets", "Bread"],
        "Rare" => ["Pickles"]
    ],
    "Mediterranean" => [
        "Frequent" => ["Olives", "Eggplant", "Zucchini"],
        "Common" => ["Parsley", "Mint", "Spinach", "Lentils", "Chickpeas"],
        "Rare" => []
    ],
    "Russian" => [
        "Frequent" => ["Potatoes", "Cabbage", "Mushrooms"],
        "Common" => ["Beets", "Pickles"],
        "Rare" => ["Dill"]
    ],
    "French" => [
        "Frequent" => ["Potatoes", "Mushrooms", "Spinach"],
        "Common" => ["Leeks", "Herbs (Thyme, Tarragon)"],
        "Rare" => []
    ],
    "Vietnamese" => [
        "Frequent" => ["Rice", "Rice Noodles", "Tofu", "Mint"],
        "Common" => ["Cilantro", "Basil", "Mushrooms", "Coconut Milk"],
        "Rare" => ["Peanuts"]
    ],
    "Brazilian" => [
        "Frequent" => ["Black Beans", "Rice", "Cassava"],
        "Common" => ["Sweet Potatoes", "Plantains", "Palm Hearts"],
        "Rare" => ["Coconut Milk", "Okra"]
    ],
    "Argentinian" => [
        "Frequent" => ["Corn", "Rice", "Potatoes"],
        "Common" => ["Pumpkin", "Lentils"],
        "Rare" => ["Squash", "Peppers"]
    ],
    "Japanese" => [
        "Frequent" => ["Rice", "Tofu", "Seaweed"],
        "Common" => ["Soybeans", "Miso", "Edamame", "Mushrooms (Shiitake, Enoki)"],
        "Rare" => ["Sweet Potatoes", "Lotus Root", "Green Tea"]
    ],
    "Spanish" => [
        "Frequent" => ["Peppers", "Eggplant", "Olives", "Rice"],
        "Common" => ["Chickpeas", "Potatoes"],
        "Rare" => ["Paprika"]
    ],
    "Korean" => [
        "Frequent" => ["Rice", "Tofu", "Soybeans", "Kimchi (vegan version)"],
        "Common" => ["Mushrooms", "Seaweed"],
        "Rare" => ["Sesame Seeds", "Gochujang"]
    ],
    "African (various regional)" => [
        "Frequent" => ["Cassava", "Yams", "Plantains", "Okra"],
        "Common" => ["Lentils", "Rice", "Peanuts", "Chickpeas"],
        "Rare" => ["Couscous", "Sweet Potatoes", "Collard Greens", "Millet", "Sorghum"]
    ],
    "Peruvian" => [
        "Frequent" => ["Corn", "Potatoes", "Quinoa"],
        "Common" => ["Beans", "Sweet Potatoes", "Avocados"],
        "Rare" => ["Cilantro", "Peppers"]
    ],
    "Greek" => [
        "Frequent" => ["Eggplant", "Olives", "Zucchini"],
        "Common" => ["Spinach", "Potatoes", "Feta Cheese"],
        "Rare" => ["Oregano"]
    ],
    "Caribbean" => [
        "Frequent" => ["Plantains", "Rice", "Beans"],
        "Common" => ["Coconut", "Sweet Potatoes", "Cassava"],
        "Rare" => ["Avocados", "Peppers", "Okra", "Callaloo"]
    ],
    "Lebanese" => [
        "Frequent" => ["Chickpeas", "Lentils", "Eggplant"],
        "Common" => ["Fava Beans", "Parsley", "Mint", "Pita Bread"],
        "Rare" => ["Tahini"]
    ],
    "Thai" => [
        "Frequent" => ["Rice", "Rice Noodles", "Tofu", "Coconut Milk"],
        "Common" => ["Chilies", "Lemongrass", "Basil", "Mint"],
        "Rare" => ["Cilantro", "Peanuts", "Mushrooms", "Bamboo Shoots"]
    ],
    "Chinese" => [
        "Frequent" => ["Tofu", "Rice", "Soybeans"],
        "Common" => ["Rice Noodles", "Bok Choy", "Mushrooms", "Spinach"],
        "Rare" => ["Peanuts", "Ginger", "Snow Peas", "Lotus Root"]
    ],
    "American" => [
        "Frequent" => ["Potatoes", "Corn", "Beans"],
        "Common" => ["Bread", "Pumpkin"],
        "Rare" => ["Sweet Potatoes", "Mushrooms"]
    ],
    "Ethiopian" => [
        "Frequent" => ["Lentils", "Chickpeas", "Cabbage", "Spinach"],
        "Common" => ["Injera"],
        "Rare" => ["Berbere Spices", "Split Peas"]
    ],
    "Italian" => [
        "Frequent" => ["Olive Oil", "Basil", "Pasta"],
        "Common" => ["Mushrooms", "Eggplant", "Zucchini"],
        "Rare" => ["Polenta", "Artichokes", "Capers", "Arugula"]
    ]
);

    // Styles applicable for vegan dishes
    $styles = array('Grilled', 'Baked', 'Roasted', 'Spicy', 'Glazed', 'Braised', 'Marinated', 'Steamed');

    // Select cuisine
    $cuisines = array_keys($cuisine_ingredients);
    $random_cuisine = $cuisines[array_rand($cuisines)];

    // Weighted selection of ingredient based on frequency
    $ingredient_list = [];
    $ingredient_list = array_merge(
        array_fill(0, 6, $cuisine_ingredients[$random_cuisine]["Frequent"]),
        array_fill(0, 3, $cuisine_ingredients[$random_cuisine]["Common"]),
        array_fill(0, 1, $cuisine_ingredients[$random_cuisine]["Rare"])
    );
    $selected_ingredient_set = $ingredient_list[array_rand($ingredient_list)];
    $random_ingredient = $selected_ingredient_set[array_rand($selected_ingredient_set)];

    // Randomly pick a cooking style
    $random_style = $styles[array_rand($styles)];

    // Generate the prompt
    $prompt = "Generate a creative and appetizing vegan dish name based on the following: $random_style $random_cuisine $random_ingredient.";

    // API request loop to ensure uniqueness
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
                    array('role' => 'system', 'content' => 'You are a chef that specializes in creating unique and creative vegan dish names.'),
                    array('role' => 'user', 'content' => $prompt)
                ),
                'max_tokens' => 50
            )),
            'timeout' => 60
        ));

        // Check API response
        if (is_wp_error($response)) {
            error_log('ChatGPT API request failed: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Extract dish name
        if (isset($data['choices'][0]['message']['content'])) {
            $dish_name = trim($data['choices'][0]['message']['content']);
            $unique_name_found = true;
        } else {
            error_log('Error in ChatGPT API response: ' . $body);
            return false;
        }
    }

    // Get category slug and tags
    $category_slug = map_cuisine_to_category_vegan($random_cuisine);
    $tags = [$random_cuisine, $random_ingredient, $random_style, "Vegan"];

    // Return dish info
    return [
        'dish_name' => $dish_name,
        'category_slug' => $category_slug,
        'tags' => $tags
    ];
}

// Map cuisine to corresponding category
function map_cuisine_to_category_vegan($cuisine) {
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
