## What's brisky

**brisky** is a static resource load framework evoluted from fisp. We have some special structure in our resource map which normally named **resource.json**. And our module loader of browser side is [kernel.js](https://github.com/AceMood/kerneljs/), which is flexible enough to load js and css file as module dynamically. 

## Why brisky

Fisp is suit for fis tool to load and manage front end resources. We have another static resource manage system named **soi**, [soi](https://github.com/Saber-Team/soi) has a very flexible plug-in archtecture, and its source code is simple enough for all developers to read. Used in production environment is also easy for not only front end developers. Further, we want to control the logic ourselves. 


## Usage

### initialize page

```
<!DOCTYPE html>
<html lang="en">
{{brisk_page_init framework="kernel"}}
<head>
```


## Tips

[1] **brisk** has a core class set in brisk directory, and other api out of this directory are all smarty plugins.

[2] **brisk** does not depend on smarty object in its core code.

[3] **phpunit test code** will be added later.