<?php
$categories = [
  [
    'id' => 1, 'name' => 'Upload Employee File to DB', 'subcategories' => [
      ['id' => 1, 'name' => '.xlsx file'],
      ['id' => 2, 'name' => '.csv file']
    ]
  ],
  [
    'id' => 2, 'name' => 'Display Employee Data', 'subcategories' => [
      ['id' => 1, 'name' => 'Active Employees Only'],
      ['id' => 2, 'name' => 'Inactive Employees Only'],
      ['id' => 3, 'name' => 'All Employees']
    ]
  ],
  [
    'id' => 3, 'name' => 'Query Database', 'subcategories' => [
      ['id' => 1, 'name' => 'SELECT', 'subcategories' => [ 'id' => 1, 'name' => '*']],
      ['id' => 2, 'name' => 'UPDATE'],
      ['id' => 3, 'name' => 'DELETE']
    ]
  ]
];

  $category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

    foreach($categories as $category) 
    {
        if($category['id'] == $category_id) 
        {
            // Code when this id matched requested id
            $subcategories = $category['subcategories'];
            foreach($subcategories as $subcategory) 
            {
                echo "<option value=\"{$subcategory['id']}\">";
                echo $subcategory['name'];
                echo "</option>";
            }
        }
    }

?>
