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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkBolt | Bundle Your World</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <style>    body { background: #020617; color: #f8fafc; font-family: sans-serif; transition: 0.3s; }

    body.light {
        background: #f8fafc;
        color: #020617;
    }

    body.light .glass {
        background: rgba(226, 232, 240, 0.8);
        border: 1px solid rgba(59, 130, 246, 0.2);
    }

    body.light .input-box {
        background: #ffffff;
        color: #020617;
        border: 1px solid #cbd5f5;
    }

    body.light .input-box::placeholder {
        color: #64748b;
    }

    .theme-btn {
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        background: #1e293b;
        color: #f8fafc;
    }

    body.light .theme-btn {
        background: #e2e8f0;
        color: #020617;
    }
        body { background: #020617; color: #f8fafc; font-family: sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(59, 130, 246, 0.2); }
        .blue-glow { box-shadow: 0 0 30px rgba(37, 99, 235, 0.2); }
        .accent-blue { background: #2563eb; }
        .accent-blue:hover { background: #1d4ed8; }
        .input-box { background: #0f172a; border: 1px solid #1e293b; color: white; transition: 0.2s; }
        .input-box:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="min-h-screen p-4 md:p-10 flex flex-col items-center">

    <?php if ($slug): ?>
        <!-- VIEWING A BUNDLE -->
        <div class="max-w-md w-full text-center mt-10">
            <?php if (isset($error)): ?>
                <h1 class="text-2xl font-bold text-red-400"><?= $error ?> LINKBOLT</h1>
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
                <div class="text-3xl font-black italic tracking-tighter text-blue-500">LINK<span class="text-white">BOLT (LINK BUNDELER)</span></div>
                <div id="user-tag" class="text-[10px] uppercase tracking-widest bg-slate-800 px-3 py-1 rounded text-slate-400"></div>

                <div class="flex items-center gap-3">
        <div id="user-tag" class="text-[10px] uppercase tracking-widest bg-slate-800 px-3 py-1 rounded text-slate-400"></div>
        <button onclick="toggleTheme()" class="theme-btn" id="theme-btn">🌙 Dark</button>
    </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Creator Panel -->
                <div class="glass p-6 rounded-3xl blue-glow">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span class="w-2 h-6 accent-blue rounded-full"></span>  Add New Bundle
                    </h2>
                    <div class="space-y-4">
                        <input type="text" id="b-name" placeholder="Bundle Name (e.g. My Socials)" class="w-full p-3 rounded-xl input-box">
                        <input type="text" id="b-slug" placeholder="custom-slug" class="w-full p-3 rounded-xl input-box">
                        
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

                        <button onclick="createBundle()" class="w-full accent-red p-4 rounded-xl font-bold text-white mt-4 shadow-lg active:scale-95 transition-all">
                            CREATE BOLT LINK
                        </button>
                    </div>
                </div>

                <!-- History Panel -->
                <div>
                    <h2 class="text-xl font-bold mb-4 text-slate-400">List of Your Active Bolts</h2>
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

            async function loadBundles() {
                const res = await fetch('api.php?action=list&user_id=' + userId);
                const bundles = await res.json();
                const container = document.getElementById('my-bundles');
                
                bundles.forEach(b => {
                    const url = window.location.origin + window.location.pathname + '?s=' + b.slug;
                    container.innerHTML += `
                        <div class="p-4 glass rounded-2xl border-l-2 border-blue-500">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-lg">${b.bundle_name}</span>
                                <span class="text-[10px] bg-blue-500/20 text-blue-400 px-2 py-1 rounded uppercase">${b.link_count} Links</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" value="${url}" readonly class="w-full bg-black/30 p-2 text-xs rounded border border-white/10 text-slate-400">
                                <button onclick="window.open('${url}')" class="text-xs bg-white text-black px-3 py-2 rounded font-bold">Open</button>
                            </div>
                        </div>
                    `;
                });
            }
            loadBundles();

            function toggleTheme() {
    const body = document.body;
    const btn = document.getElementById("theme-btn");

    body.classList.toggle("light");

    if(body.classList.contains("light")) {
        btn.innerText = "☀ Light";
        localStorage.setItem("lb_theme", "light");
    } else {
        btn.innerText = "🌙 Dark";
        localStorage.setItem("lb_theme", "dark");
    }
}

// Apply saved theme on load
const savedTheme = localStorage.getItem("lb_theme");

if(savedTheme === "light") {
    document.body.classList.add("light");
    document.getElementById("theme-btn").innerText = "☀ Light";
}

        </script>
    <?php endif; ?>
</body>
</html>
