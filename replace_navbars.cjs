const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources', 'views');

function processDirectory(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        if (stat.isDirectory()) {
            if (!fullPath.includes('components')) {
                processDirectory(fullPath);
            }
        } else if (file.endsWith('.blade.php') && !fullPath.includes('components')) {
            let content = fs.readFileSync(fullPath, 'utf8');
            let originalContent = content;

            // Regex for old header (<!-- Top Bar --> up to the end of <!-- Mobile Menu Modal -->)
            // It starts with <!-- Top Bar --> and we want to remove everything until the closing </div> of the modal
            // The modal ends right before <main> or <!-- Hero Section --> or similar.
            const regexOld = /[\t ]*<!-- Top Bar -->[\s\S]*?<!-- Mobile Menu Modal -->[\s\S]*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*/;
            
            // Regex for the new header in home/index.blade.php that we just replaced
            const regexNew = /[\t ]*<!-- 1\. Top Bar \(Ringkas & Tipis\) -->[\s\S]*?<!-- 4\. Mobile Offcanvas Menu[\s\S]*?<\/div>\s*<\/div>\s*/;

            if (regexOld.test(content)) {
                content = content.replace(regexOld, '\n    @include(\'components.navbar\')\n\n');
                console.log(`Replaced old navbar in ${fullPath}`);
            } else if (regexNew.test(content) && !fullPath.endsWith('navbar.blade.php')) {
                content = content.replace(regexNew, '\n    @include(\'components.navbar\')\n\n');
                console.log(`Replaced new navbar block with include in ${fullPath}`);
            }

            // Also, remove <div id="navbar-sentinel" class="navbar-sentinel"></div> if it exists manually (since our JS creates it dynamically)
            const sentinelRegex = /<div id="navbar-sentinel" class="navbar-sentinel"><\/div>\s*/g;
            if (sentinelRegex.test(content)) {
                content = content.replace(sentinelRegex, '');
            }

            if (content !== originalContent) {
                fs.writeFileSync(fullPath, content, 'utf8');
            }
        }
    }
}

processDirectory(viewsDir);
console.log("Done!");
