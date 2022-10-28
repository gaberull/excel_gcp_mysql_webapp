<?php
if(!isset($_SESSION)) session_start();

if(!isset($_SESSION['username']))
{
  header('Location: ./login.php');
  die();
  exit;
}
// Categories for upload menu options
$categories = 
[
  ['id' => 1, 'name' => 'Upload Employee File to DB', 'subcategories' => 
    [
      ['id' => 1, 'name' => '.xlsx file'],
      ['id' => 2, 'name' => '.csv file']
    ]
  ],
  [
    'id' => 2, 'name' => 'Display Employee Data', 'subcategories' => 
    [
      ['id' => 1, 'name' => 'Select Option'],
      ['id' => 2, 'name' => 'Active Employees Only'],
      ['id' => 3, 'name' => 'Inactive Employees Only'],
      ['id' => 4, 'name' => 'Upcoming Birthdays', 'subcategories' => 
        [
          ['id' => 1, 'name' => '7 days out'],
          ['id' => 2, 'name' => '14 days out'],
          ['id' => 3, 'name' => '30 days out'],
          ['id' => 4, 'name' => '60 days out']
        ],
      ],
      ['id' => 5, 'name' => 'All Employees']
    ]
  ],
  ['id' => 3, 'name' => 'Query Database', 'subcategories' => 
    [
      ['id' => 1, 'name' => 'SELECT', 'subcategories' => 
        [
          ['id' => 1, 'first_name' => '*']
        ]
      ],
      ['id' => 2, 'name' => 'UPDATE'],
      ['id' => 3, 'name' => 'DELETE']
    ]
  ]
];


  $category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
  if(isset($_GET['category_id']))
  {
      foreach($categories as $category) 
      {
          if($category['id'] == $category_id) // if this id matches requested id
          {
            $subcategories = $category['subcategories'];
            foreach($subcategories as $subcategory) 
            {
              if($subcategory['name']=='Select Option')
              {
                  echo "<option disabled selected value=\"{$subcategory['id']}\">";
                  echo $subcategory['name'];
                  echo "</option>";
              }
              else
              {
                  echo "<option value=\"{$subcategory['id']}\">";
                  echo $subcategory['name'];
                  echo "</option>";
              }
                
            }
          }
      }
  }
  else if(isset($_GET['subcategory_id']))
  {
    $input =  $_GET['subcategory_id'];
    $input_exploded = explode(',', $input);
    $category_id = $input_exploded[0];
    $subcategory_id = $input_exploded[1];
    foreach($categories as $category) 
    {
        if($category['id'] == $category_id) 
        {
            $subcategories = $category['subcategories'];
            foreach($subcategories as $subcategory) 
            {
                if($subcategory['id'] == $subcategory_id)
                {
                  if(is_array( $subcategory['subcategories'] ) || is_object( $subcategory['subcategories'] ))
                  {
                      $subsubcategories = $subcategory['subcategories'];
                      foreach($subsubcategories as $subsubcategory)
                      {
                        if($subsubcategory['id'] == 1)
                        {
                            echo "<option selected value=\"{$subsubcategory['id']}\">";
                            echo $subsubcategory['name'];
                            echo "</option>";
                        }
                        else
                        {
                            echo "<option value=\"{$subsubcategory['id']}\">";
                            echo $subsubcategory['name'];
                            echo "</option>";
                        }
                      }
                  }
                  else
                  {
                      //note: hiding this here doesn't seem to be effective on the webpage
                      echo "<option hidden></option>";  
                  }
                }
            }
        }
    }
  }
?>
