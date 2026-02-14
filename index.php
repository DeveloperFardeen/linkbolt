<?php
require 'pdo.php';

// Check if viewing a bundle
$slug = $_GET['s'] ?? null;
if ($slug) {
    $stmt = $pdo->prepare("SELECT b.bundle_name, l.link_title, l.destination_url 
                           FROM bundles b 
                           JOIN bundle_links l ON b.id = l.bundle_id 
                           WHERE b.slug = ?");
    $stmt->execute([$slug]);
    $bundle_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$bundle_data) { $error = "Bundle not found."; }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bolt Link Creator</title>

<style>
body{
margin:0;
font-family:sans-serif;
background:linear-gradient(135deg,#141e30,#243b55);
display:flex;
justify-content:center;
align-items:center;
height:100vh;
color:white;
}

.box{
background:rgba(255,255,255,0.08);
padding:25px;
border-radius:15px;
backdrop-filter:blur(12px);
box-shadow:0 8px 25px rgba(0,0,0,0.4);
width:320px;
}

h2{
text-align:center;
}

input{
width:100%;
padding:10px;
margin-top:10px;
border-radius:8px;
border:none;
background:#1f2937;
color:white;
}

button{
width:100%;
padding:12px;
margin-top:15px;
border:none;
border-radius:10px;
background:linear-gradient(90deg,#00c6ff,#0072ff);
color:white;
font-size:16px;
cursor:pointer;
transition:0.3s;
}

button:hover{
transform:scale(1.05);
}
</style>
</head>

<body>

<div class="box">
<h2>🚀 New Bundle</h2>

<input type="text" placeholder="Bundle Name">
<input type="text" placeholder="Custom Slug">
<input type="text" placeholder="Title">
<input type="url" placeholder="URL">

<button>Create Bolt Link ⚡</button>
</div>

</body>
</html>
<body class="min-h-screen p-4 md:p-10 flex flex-col items-center">

    <?php if ($slug): ?>
        <!-- VIEWING A BUNDLE -->
        <div class="max-w-md w-full text-center mt-10">
            <?php if (isset($error)): ?>
                <h1 class="text-2xl font-bold text-red-400"><?= $error ?></h1>
                <a href="index.php" class="text-blue-400 underline mt-4 block">Go Back</a>
            <?php else: ?>
                <div class="mb-8">
                    <div class="w-16 h-16 accent-blue rounded-2xl mx-auto mb-4 flex items-center justify-center rotate-3 blue-glow">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <h1 class="text-3xl font-black uppercase tracking-tighter"><?= htmlspecialchars($bundle_data[0]['bundle_name']) ?></h1>
                    <p class="text-slate-400 text-sm">Created via LinkBolt</p>
                </div>
                <div class="space-y-4">
                    <?php foreach ($bundle_data as $link): ?>
                        <a href="<?= htmlspecialchars($link['destination_url']) ?>" target="_blank" 
                           class="block p-5 glass rounded-2xl font-bold hover:scale-[1.02] transition-transform border-l-4 border-l-blue-500">
                            <?= htmlspecialchars($link['link_title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- DASHBOARD -->
        <div class="max-w-2xl w-full">
            <header class="flex justify-between items-center mb-12">
                <div class="text-3xl font-black italic tracking-tighter text-blue-500">LINK<span class="text-white">BOLT</span></div>
                <div id="user-tag" class="text-[10px] uppercase tracking-widest bg-slate-800 px-3 py-1 rounded text-slate-400"></div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Creator Panel -->
                <div class="glass p-6 rounded-3xl blue-glow">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span class="w-2 h-6 accent-blue rounded-full"></span> New Bundle
                    </h2>
                    <div class="space-y-4">
                        <input type="text" id="b-name" placeholder="Bundle Name (e.g. My Socials)" class="w-full p-3 rounded-xl input-box">
                        <input type="text" id="b-slug" placeholder="Create your bundle name as shown above" class="w-full p-3 rounded-xl input-box">
                        
                        <div class="pt-4 border-t border-slate-700">
                            <p class="text-xs text-slate-500 mb-2 uppercase font-bold">Add Links to this bundle</p>
                            <div id="links-builder" class="space-y-2 mb-4">
                                <div class="flex gap-2">
                                    <input type="text" placeholder="Title" class="w-1/3 p-2 text-sm rounded-lg input-box link-title-in">
                                    <input type="url" placeholder="URL" class="w-2/3 p-2 text-sm rounded-lg input-box link-url-in">
                                </div>
                            </div>
                            <button onclick="addLinkField()" class="text-xs text-blue-400 hover:text-blue-300">+ Add Another URL</button>
                        </div>

                        <button onclick="createBundle()" class="w-full accent-blue p-4 rounded-xl font-bold text-white mt-4 shadow-lg active:scale-95 transition-all">
                            CREATE BOLT LINK
                        </button>
                    </div>
                </div>

                <!-- History Panel -->
                <div>
                    <h2 class="text-xl font-bold mb-4 text-slate-400">Your Active Bolts</h2>
                    <div id="my-bundles" class="space-y-4">
                        <!-- Loaded via JS -->
                    </div>
                </div>
            </div>
        </div>

        <script>
            let userId = localStorage.getItem('lb_uid') || 'u_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('lb_uid', userId);
            document.getElementById('user-tag').innerText = "ID: " + userId;

            function addLinkField() {
                const div = document.createElement('div');
                div.className = 'flex gap-2';
                div.innerHTML = `<input type="text" placeholder="Title" class="w-1/3 p-2 text-sm rounded-lg input-box link-title-in">
                                 <input type="url" placeholder="URL" class="w-2/3 p-2 text-sm rounded-lg input-box link-url-in">`;
                document.getElementById('links-builder').appendChild(div);
            }

            async function createBundle() {
                const name = document.getElementById('b-name').value;
                const slug = document.getElementById('b-slug').value;
                const titles = Array.from(document.querySelectorAll('.link-title-in')).map(i => i.value);
                const urls = Array.from(document.querySelectorAll('.link-url-in')).map(i => i.value);

                const links = titles.map((t, i) => ({ title: t, url: urls[i] })).filter(l => l.title && l.url);

                const res = await fetch('api.php?action=create', {
                    method: 'POST',
                    body: JSON.stringify({ user_id: userId, name, slug, links })
                });
                const out = await res.json();
                if(out.success) {
                    location.reload();
                } else {
                    alert(out.error || "Slug already taken or error occurred.");
                }
            }
function isValidURL(url) {
    try {
        const parsed = new URL(url);
        return parsed.protocol === "http:" || parsed.protocol === "https:";
    } catch (err) {
        return false;
    }
}

           async function createBundle() {
    const name = document.getElementById('b-name').value.trim();
    const slug = document.getElementById('b-slug').value.trim();

    const titles = Array.from(document.querySelectorAll('.link-title-in')).map(i => i.value.trim());
    const urls = Array.from(document.querySelectorAll('.link-url-in')).map(i => i.value.trim());

    const links = [];

    for (let i = 0; i < titles.length; i++) {
        if (titles[i] && urls[i]) {

            if (!isValidURL(urls[i])) {
                alert("Invalid URL: " + urls[i] + "\nPlease enter a valid http or https link.");
                return;
            }

            links.push({ title: titles[i], url: urls[i] });
        }
    }

    if (links.length === 0) {
        alert("Please add at least one valid link.");
        return;
    }

    const res = await fetch('api.php?action=create', {
        method: 'POST',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_id: userId, name, slug, links })
    });

    const out = await res.json();

    if (out.success) {
        location.reload();
    } else {
        alert(out.error || "Error occurred.");
    }
}

                
                });
            }
            loadBundles();
        </script>
    <?php endif; ?>
</body>
</html>
