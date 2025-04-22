<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
   header("Location: ../../documents.php");
   exit();
}

$id = sanitize($_GET['id']);

// Get document information for logging
$document_query = "SELECT document_id FROM documents WHERE id = $id";
$document_result = $conn->query($document_query);

if ($document_result && $document_result->num_rows > 0) {
   $document = $document_result->fetch_assoc();
   $document_id = $document['document_id'];
   
   // Delete the document
   $sql = "DELETE FROM documents WHERE id = $id";
   if ($conn->query($sql) === TRUE) {
       // Log activity
       logActivity($_SESSION['user_id'], "Deleted document: $document_id");
       
       // Redirect to documents page with success message
       header("Location: ../../documents.php?success=3");
       exit();
   } else {
       // Redirect with error
       header("Location: ../../documents.php?error=1");
       exit();
   }
} else {
   // Redirect with error
   header("Location: ../../documents.php?error=1");
   exit();
}
?>
