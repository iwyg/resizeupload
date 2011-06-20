# Resize Uploaded Image Files

## Description

This might save you some headache if you experience your client uploading ridiculously large images 
thus running out of memory on the shared Hosting Server when using JIT Image Manipulation.

It will convert the uploaded image when creating a new entry or saving an existing one if 
the file exceeds a given maximum width and/or height using ImageMagick. 


## Usage

Install the extension, then go to `System->Preferences` and specify the path to ImageMagick and the maximum file width and file height.

![](https://github.com/iwyg/resizeupload/raw/master/docs/resizeupload_settings.png)

## Changelog
- 2011-06-18: initial release
- 2011-06-20: entries_data  table now gets updated with the corrected file meta data
