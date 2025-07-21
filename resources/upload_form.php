<h4>Upload Resource</h4>
<form action="resources/upload_resource.php" method="POST" enctype="multipart/form-data">
    <label>Select File (Video/Image):</label>
    <input type="file" name="resource_file" class="form-control" required><br>
    
    <label>Or enter a Link:</label>
    <input type="text" name="resource_link" class="form-control"><br>

    <button type="submit" class="btn btn-info">Send Resource</button>
</form>
