<?php 
function id($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $uid = '';
    
    for ($i = 0; $i < $length; $i++) {
        $uid .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $uid;
}
// Search function

function searchContacts($contacts, $query = '', $category = '', $gender = '') {
    $results = [];
    
    // If no search query and no filters, return all contacts
    if(empty($query) && empty($category) && empty($gender)) {
        return $contacts;
    }
    
    foreach($contacts as $contact) {
        $match = true;
        
        // Text search with partial matching
        if(!empty($query)) {
            $search_match = false;
            $query_lower = strtolower(trim($query));
            
            // Search in multiple fields with partial matching
            $search_fields = [
                'first_name' => $contact['first_name'] ?? '',
                'last_name' => $contact['last_name'] ?? '', 
                'phone' => $contact['phone'] ?? '',
                'category' => $contact['category'] ?? '',
                'gender' => $contact['gender'] ?? '',
                'id' => $contact['id'] ?? ''
            ];
            
            foreach($search_fields as $field_value) {
                if(!empty($field_value) && stripos(strtolower($field_value), $query_lower) !== false) {
                    $search_match = true;
                    break;
                }
            }
            
            // Also check combined first + last name
            $full_name = ($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '');
            if(stripos(strtolower(trim($full_name)), $query_lower) !== false) {
                $search_match = true;
            }
            
            if(!$search_match) {
                $match = false;
            }
        }
        
        // Category filter
        if(!empty($category) && $match) {
            if(!isset($contact['category']) || $contact['category'] !== $category) {
                $match = false;
            }
        }
        
        // Gender filter
        if(!empty($gender) && $match) {
            if(!isset($contact['gender']) || $contact['gender'] !== $gender) {
                $match = false;
            }
        }
        
        if($match) {
            $results[] = $contact;
        }
    }
    
    return $results;
}

// Enhanced function with better partial matching for names


// Combined search function that uses both methods


function highlightSearchTerm($text, $query) {
    if(empty($query) || empty(trim($query))) return $text;
    
    $pattern = '/(' . preg_quote(trim($query), '/') . ')/i';
    return preg_replace($pattern, '<mark class="bg-warning">$1</mark>', $text);
}

?>