<!DOCTYPE html>

<html lang="en">
<!-- Description: AR Arrangement -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 28/11/2025 -->
<!-- Validated: OK 6/12/2025 -->

<head>
    <title>AR Floral Arrangement | Root Flower</title>
	<meta charset="utf-8"/>
	<meta name="author" content="Joanne Chin Jia Xuan"/>
	<meta name="description" content="Root Flower is a creative florist hub offering fresh floral products, inspiring workshops, and a platform for students to showcase their floral artistry. Discover, learn, and create with us.">
    <meta name="keywords" content="Root Flower, florist, kuching florist, flower, flower bouquet, florist workshop"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<link rel="icon" type="image/x-icon" href="img/favicon.ico"/> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"> <!-- Bootstrap icon link-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script> <!-- Three.js -->
    
    <!-- MediaPipe Hands -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="style/style.css"/>
</head>

<body>
    <!-- Loading Screen -->
    <div id="loading-screen" class="w-100 h-100 position-absolute top-0 start-0 d-flex flex-column justify-content-center align-items-center bg-light">
        <img src="img/rootflower.jpg" alt="Root Flower Logo">
        <p class="mb-4 fs-1">Root Flower AR Florist</p>
        <p class="text-secondary">Loading AI Models & 3D Engine...</p>
        <div class="w-50 bg-secondary-subtle rounded-pill overflow-hidden">
            <div id="loading-bar" class="h-100 bg-warning"></div>
        </div>
    </div>

    <!-- Main Canvas -->
    <div id="canvas-container" class="position-absolute top-0 end-0 w-100 h-100 z-1"></div>
    
    <!-- User Camera Feed (Mirror) -->
    <video id="video-feed" playsinline class="position-absolute rounded border object-fit-cover border-secondary z-1"></video>

    <!-- UI Sidebar -->
    <div id="ui-sidebar" class="glass-panel rounded position-absolute h-100 overflow-y-auto">
        <h1 class="fs-5 fw-bold d-flex align-items-center gap-2 mb-3">🌸 AR Florist Studio</h1>

        <!-- AI Assistant Section -->
        <div class="glass-panel rounded ai-panel p-3 mb-3">
            <h2 class="fs-3 fw-bold text-purple-300 mb-3">AI Floral Muse</h2>
            
            <!-- Suggestion Tab -->
            <div class="mb-3">
                <input type="text" id="ai-prompt" placeholder="Occasion (e.g. First Date)..." class="form-control form-control-sm bg-light bg-opacity-50 rounded-3 mb-3">
                <button onclick="askGeminiSuggestion()" class="btn btn-sm w-100 text-uppercase fw-semibold tracking-wider text-secondary" id="suggestion-btn">
                    SUGGEST FLOWERS
                </button>
            </div>

            <div class="d-flex gap-2">
                <button onclick="askGeminiAnalysis()" id="btn-interpret" class="btn btn-sm w-100 text-uppercase fw-semibold tracking-wider text-secondary">
                    INTERPRET MEANING
                </button>
            </div>

            <!-- Output Area -->
            <div id="ai-output" class="mt-3 d-none fs-5 bg-white bg-opacity-25 text-dark rounded mt-1 p-2 lh-1"></div>
        </div>

        <!-- Container Selection -->
        <div class="mb-3">
            <h2 class="fw-bolder mb-2">Container</h2>
            <select id="container-select" class="form-select border-white-50 py-0">
                <option value="pot">Classic Pot</option>
                <option value="vase">Crystal Vase</option>
                <option value="wreath">Wreath Base</option>
                <option value="basket">Wicker Basket</option>
                <option value="bouquet">Bouquet Wrap</option>
            </select>
        </div>

        <!-- Flower Color -->
        <div class="mb-3">
            <h2 class="fw-bolder mb-2">Flower Color</h2>
            <div class="d-flex flex-wrap gap-2" id="color-palette">
                <!-- Colors injected by JS -->
            </div>
        </div>

        <!-- Flower Type -->
        <div class="mb-3">
            <h2 class="fw-bolder mb-2">Flower Type</h2>
            <div class="row row-cols-2 g-2" id="flower-grid">
                <!-- Buttons injected by JS -->
            </div>
        </div>

        <div class="pt-3 border-top border-black-50">
            <button onclick="screenshotArrangement()" class="btn btn-sm w-100 mb-2 btn-primary">
                Take Screenshot (PNG)
            </button>
            <button onclick="resetArrangement()" class="btn btn-sm bg-white bg-opacity-50 w-100">
                Clear Arrangement
            </button>
        </div>
    </div>

    <!-- Exit Button -->
    <div class="position-absolute bottom-0 end-0 m-4 z-3">
        <a class="btn btn-primary" href="products.php">Back to Products&ensp;<i class="bi bi-box-arrow-right align-baseline"></i></a>
    </div>

    <!-- Gesture Hints -->
    <div id="gesture-hint" class="glass-panel rounded p-3 text-center pe-none position-absolute">
        <div class="d-flex justify-content-center align-items-center gap-4">
            <div class="d-flex flex-column align-items-center">
                <span class="fs-4">🖱️</span>
                <span class="fw-bold mt-1 movement-txt">SCROLL</span>
                <span class="opacity-75 movement-txt2">Zoom In/Out</span>
            </div>

            <div class="d-flex flex-column align-items-center">
                <span class="fs-4">👌</span>
                <span class="fw-bold mt-1 movement-txt">PINCH</span>
                <span class="opacity-75 movement-txt2">Place / Move</span>
            </div>
            <div class="d-flex flex-column align-items-center">
                <span class="fs-4">👋</span>
                <span class="fw-bold mt-1 movement-txt">DRAG AWAY</span>
                <span class="opacity-75 movement-txt2">to Delete</span>
            </div>
            <div class="d-flex flex-column align-items-center">
                <span class="fs-4">✊</span>
                <span class="fw-bold mt-1 movement-txt">FIST</span>
                <span class="opacity-75 movement-txt2">Rotate & Tilt</span>
            </div>
        </div>
        <div id="status-msg" class="text-center text-primary small mt-2 fw-mono h-4">Detecting hands...</div>
    </div>

    <script>
        const apiKey = "<?php echo getenv('GOOGLE_API_KEY') ?: ''; ?>";

        // CONFIGURATION & STATE 
        const FLOWERS = [
            { id: 'rose', name: 'Rose', type: 'layered', petals: 15, shape: 'organic' },
            { id: 'tulip', name: 'Tulip', type: 'cup', petals: 6, shape: 'smooth' },
            { id: 'daisy', name: 'Daisy', type: 'flat', petals: 18, shape: 'long', centerSize: 0.2 },
            { id: 'sunflower', name: 'Sunflower', type: 'flat', petals: 24, centerSize: 0.4, centerColor: 0x6e2c00 },
            { id: 'lily', name: 'Lily', type: 'star', petals: 6, shape: 'sharp' },
            { id: 'orchid', name: 'Orchid', type: 'complex', petals: 5, shape: 'exotic' },
            { id: 'hydrangea', name: 'Hydrangea', type: 'cluster', petals: 40 },
            { id: 'peony', name: 'Peony', type: 'layered', petals: 25, shape: 'messy' },
            { id: 'poppy', name: 'Poppy', type: 'cup', petals: 4, shape: 'wide' },
            { id: 'daffodil', name: 'Daffodil', type: 'trumpet', petals: 6, centerSize: 0.25, centerColor: 0xFF8C00 },
            { id: 'carnation', name: 'Carnation', type: 'ruffle', petals: 30 },
            { id: 'lavender', name: 'Lavender', type: 'spike', petals: 60, centerColor: 0x9b59b6 },
            { id: 'iris', name: 'Iris', type: 'complex', petals: 6, shape: 'fan' },
            { id: 'pansy', name: 'Pansy', type: 'flat', petals: 5, centerSize: 0.1 },
            { id: 'marigold', name: 'Marigold', type: 'ruffle', petals: 40, centerColor: 0xd35400 },
            { id: 'zinnia', name: 'Zinnia', type: 'layered', petals: 20 },
            { id: 'cosmos', name: 'Cosmos', type: 'flat', petals: 8 },
            { id: 'dahlia', name: 'Dahlia', type: 'geometric', petals: 30 },
            { id: 'lotus', name: 'Lotus', type: 'cup', petals: 12, shape: 'pointy' },
            { id: 'anemone', name: 'Anemone', type: 'flat', petals: 10, centerSize: 0.2, centerColor: 0x222222 }
        ];

        const COLORS = [
            '#841313', '#fdcde5', '#800080', '#2f7aac', '#FFD700', '#FFFFFF', '#FF8C00', '#9a8189', '#badff7'
        ];

        const state = {
            container: 'pot',
            flowerId: 'rose',
            color: '#841313',
            isPinching: false,
            isFist: false,
            handPosition: new THREE.Vector3(),
            rotationAngle: 0,
            draggedObject: null, 
            originalMaterial: null,
            highlightedObject: null,
        };

        // THREE.JS SETUP
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xe3e3e3); 
        
        const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
        camera.position.set(0, 1.5, 4);
        camera.lookAt(0, 0, 0);

        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, preserveDrawingBuffer: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMap.enabled = true;
        document.getElementById('canvas-container').appendChild(renderer.domElement);

        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);

        const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
        dirLight.position.set(5, 10, 7);
        dirLight.castShadow = true;
        scene.add(dirLight);
        
        const spotLight = new THREE.SpotLight(0xffaa00, 0.5);
        spotLight.position.set(-5, 5, 0);
        scene.add(spotLight);

        // Groups
        const mainGroup = new THREE.Group(); // Holds container + flowers
        scene.add(mainGroup);
        mainGroup.position.y = -0.7;
        
        const cursorMesh = new THREE.Mesh(
            new THREE.SphereGeometry(0.05, 16, 16),
            new THREE.MeshBasicMaterial({ color: 0x00ff00, transparent: true, opacity: 0.5 })
        );
        scene.add(cursorMesh);

        const ZOOM_SENSITIVITY = 0.0005;
        const cameraControls = {
            distance: camera.position.z, 
            targetX: 0,
            targetY: 0,
            lastMouseX: 0,
            lastMouseY: 0,
        };
        
        function updateCameraAndScene() {
            // Apply camera zoom 
            camera.position.z = cameraControls.distance;
            camera.updateProjectionMatrix(); 
        }

        // PROCEDURAL GENERATION ENGINE
        const materials = {};
        function getRealisticMat(color, roughness = 0.8, metalness = 0.0, clearcoat = 0, clearcoatRoughness = 0) {
            const key = color + roughness + metalness + clearcoat + clearcoatRoughness;
            if(!materials[key]) {
                materials[key] = new THREE.MeshPhysicalMaterial({ 
                    color: color, 
                    side: THREE.DoubleSide, 
                    roughness: roughness, 
                    metalness: metalness,
                    clearcoat: clearcoat,
                    clearcoatRoughness: clearcoatRoughness
                });
            }
            return materials[key];
        }

        function createPetalShape(width = 0.2, height = 0.5, segments = 8, curvature = 0.1, tipPointy = 0.5) {
            const shape = new THREE.Shape();
            const halfWidth = width / 2;

            shape.moveTo(-halfWidth, -height / 2);
            shape.bezierCurveTo(
                -halfWidth - curvature, -height / 4,
                -halfWidth - curvature, height / 4,
                -halfWidth / 2, height / 2 * (1 - tipPointy)
            );
            shape.lineTo(0, height / 2); // Pointy tip
            shape.bezierCurveTo(
                halfWidth / 2, height / 2 * (1 - tipPointy),
                halfWidth + curvature, height / 4,
                halfWidth + curvature, -height / 4
            );
            shape.lineTo(halfWidth, -height / 2);
            shape.closePath();

            const geometry = new THREE.ShapeGeometry(shape, segments);
            geometry.rotateX(Math.PI / 2); 

            // Add slight random curl
            const position = geometry.attributes.position;
            const vector = new THREE.Vector3();
            for (let i = 0; i < position.count; i++) {
                vector.fromBufferAttribute(position, i);
                vector.z += Math.sin((vector.y / (height/2)) * Math.PI) * (0.02 + Math.random() * 0.03); // Curl along height
                position.setZ(i, vector.z);
            }

            geometry.computeVertexNormals();
            return geometry;
        }

        // Function to create a simple, long leaf shape for wreath
        function createLeafGeometry() {
            const shape = new THREE.Shape();
            
            shape.moveTo(0, 0);
            shape.quadraticCurveTo(0.1, 0.5, 0, 1.0); // Right side curve
            shape.quadraticCurveTo(-0.1, 0.5, 0, 0); // Left side curve
            
            // Extrude the shape slightly to give it thickness
            const geometry = new THREE.ExtrudeGeometry(shape, {
                depth: 0.01,
                bevelEnabled: false
            });
            
            geometry.scale(0.3, 0.3, 0.3); 
            return geometry;
        }

        // Woven texture for basket
        function createWovenTexture() {
            const size = 64; 
            const canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;
            const context = canvas.getContext('2d');

            // Base color
            context.fillStyle = '#C19A6B'; 
            context.fillRect(0, 0, size, size);

            // Draw the weave pattern (simple diagonal stripes)
            context.strokeStyle = '#99734D'; 
            context.lineWidth = 2;
            
            // Draw diagonal lines
            for (let i = 0; i < size; i += 8) {
                context.beginPath();
                context.moveTo(i, 0);
                context.lineTo(i + size, size);
                context.stroke();
            }
            
            const texture = new THREE.CanvasTexture(canvas);
            texture.wrapS = THREE.RepeatWrapping;
            texture.wrapT = THREE.RepeatWrapping;
            texture.repeat.set(10, 10); 
            return texture;
        }

        // Wrinkling for bouquet papers
        function applyWrinkling(geometry) {
            const positionAttribute = geometry.getAttribute('position');
            const vertex = new THREE.Vector3();
            
            for (let i = 0; i < positionAttribute.count; i++) {
                vertex.fromBufferAttribute(positionAttribute, i);
                
                const radius = Math.sqrt(vertex.x * vertex.x + vertex.z * vertex.z);
                
                // Random displacement in the radial direction
                const wobble = (Math.random() - 0.5) * 0.04; 

                if (radius > 0) {
                    // Apply radial displacement
                    const factor = (radius + wobble) / radius;
                    vertex.x *= factor;
                    vertex.z *= factor;
                }

                // Random displacement in the Y direction (up/down)
                vertex.y += (Math.random() - 0.5) * 0.01; 

                positionAttribute.setXYZ(i, vertex.x, vertex.y, vertex.z);
            }
            
            // Flag the geometry needs recalculation
            geometry.attributes.position.needsUpdate = true;
            geometry.computeVertexNormals(); // Recalculate lighting information
        }

        function createContainer(type) {
            const existing = mainGroup.getObjectByName("container");
            if (existing) mainGroup.remove(existing);

            let mesh;
            
            if (type === 'pot') {
                mesh = new THREE.Mesh(
                    new THREE.CylinderGeometry(0.7, 0.55, 1.2, 32), 
                    getRealisticMat(0x8f4438, 0.9, 0.0, 0, 0)
                );
                
                // Add a simple lip/rim 
                const rim = new THREE.Mesh(
                    new THREE.TorusGeometry(0.725, 0.08, 8, 32), 
                    getRealisticMat(0x8f4438, 0.9, 0.0, 0, 0)
                );
                rim.rotation.x = Math.PI / 2;
                rim.position.y = 0.6; 
                mesh.add(rim);
            } else if (type === 'vase') {
                const points = [];
                for ( let i = 0; i < 10; i ++ ) {
                    points.push( new THREE.Vector2( Math.sin( i * 0.5 ) * 0.1 + 0.3, ( i - 5 ) * 0.2 ) );
                }
                const geo = new THREE.LatheGeometry( points, 20 );

                mesh = new THREE.Mesh(geo, new THREE.MeshPhysicalMaterial({ 
                    color: 0x182c25, 
                    transmission: 0.4, 
                    opacity: 1, 
                    transparent: true, 
                    metalness: 0.0, 
                    roughness: 0.1,
                    ior: 1.5,
                    thickness: 0.2
                }));
                mesh.scale.set(1.5,1.5,1.5);
            } else if (type === 'basket') {
                const wovenMap = createWovenTexture();
                
                const baseMaterial = new THREE.MeshStandardMaterial({
                    map: wovenMap, 
                    roughness: 0.9,
                    metalness: 0.0,
                });
                
                const darkMaterial = new THREE.MeshStandardMaterial({
                    color: 0x5d4037, 
                    roughness: 0.6,
                    metalness: 0.0,
                }); 

                const base = new THREE.Mesh(
                    new THREE.CylinderGeometry(0.8, 0.75, 0.7, 24), 
                    baseMaterial 
                );
                base.position.y = 0.35; 

                const bottomCap = new THREE.Mesh(
                    new THREE.SphereGeometry(0.75, 24, 24, 0, Math.PI * 2, 0, Math.PI / 4), 
                    baseMaterial 
                );
                bottomCap.position.y = -0.7;
                base.add(bottomCap);

                const rimGeo = new THREE.TorusGeometry(0.8, 0.05, 12, 30); 
                const rim = new THREE.Mesh(rimGeo, darkMaterial);
                rim.rotation.x = Math.PI / 2;
                rim.position.y = 0.35; 
                base.add(rim);

                const handleGeo = new THREE.TorusGeometry(0.8, 0.05, 12, 30, Math.PI); 
                const handle = new THREE.Mesh(handleGeo, darkMaterial);
                handle.position.y = 0.3; 
                
                base.add(handle);
                mesh = base;
            } else if (type === 'wreath') {
                const leafMat = getRealisticMat(0x0a2e19, 0.7, 0.0, 0, 0);
                mesh = new THREE.Group();
                const wreathGroup = new THREE.Group(); 
                const leafGeo = createLeafGeometry();
                
                const numLeaves = 5000; 
                const innerRadius = 0.9; 
                const outerRadius = 1.4; 
                const thickness = 0.3; 
                
                for (let i = 0; i < numLeaves; i++) {
                    const currentRadius = innerRadius + Math.random() * (outerRadius - innerRadius);
                    const angle = Math.random() * Math.PI * 2;
                    
                    const x = Math.cos(angle) * currentRadius;
                    const y = Math.sin(angle) * currentRadius;
                    const z = (Math.random() * thickness) - (thickness / 2); 

                    const leaf = new THREE.Mesh(leafGeo, leafMat);
                    leaf.position.set(x, y, z);

                    leaf.rotation.z = angle + Math.PI / 2;
                    leaf.rotation.y = (Math.random() - 0.5) * Math.PI / 3; 
                    leaf.rotation.x = (Math.random() - 0.5) * Math.PI / 4; 

                    wreathGroup.add(leaf);
                }
                wreathGroup.rotation.x = Math.PI / 2;
                mesh.add(wreathGroup);
                mesh.position.y = 0;
            } else if (type === 'bouquet') {
                const height = 1.5;
                const topRadius = 0.65;
                
                const points = [];
                
                points.push(new THREE.Vector2(0.01, -height / 2)); 
                points.push(new THREE.Vector2(0.1, -height / 3));
                points.push(new THREE.Vector2(0.4, 0.0));
                points.push(new THREE.Vector2(topRadius, height / 3)); 

                const primaryGeo = new THREE.LatheGeometry(points, 64);

                applyWrinkling(primaryGeo);
                
                const wrapMat = getRealisticMat(0xFBEDEA, 0.8, 0.0, 0, 0); 
                const primaryMesh = new THREE.Mesh(primaryGeo, wrapMat);
                
                const scaleFactor = 0.5; 
    
                const secondaryPoints = [];
                points.forEach(p => {
                    const scaledX = p.x * scaleFactor;
                    const finalX = p.x === 0.01 ? p.x : scaledX; 
                    const scaledY = p.y * scaleFactor;
                    
                    secondaryPoints.push(new THREE.Vector2(finalX, scaledY));
                });

                const secondaryGeo = new THREE.LatheGeometry(secondaryPoints, 64);
                applyWrinkling(secondaryGeo);

                const secondaryMat = getRealisticMat(0xFBEDEA, 0.7, 0.0, 0, 0); 
                const secondaryMesh = new THREE.Mesh(secondaryGeo, secondaryMat);

                secondaryMesh.scale.y = -1; 
                secondaryMesh.position.y = -height * scaleFactor;

                const bouquetWrapGroup = new THREE.Group();
                bouquetWrapGroup.add(primaryMesh);
                bouquetWrapGroup.add(secondaryMesh);

                mesh = bouquetWrapGroup;
                mesh.position.y = 0;
            }

            mesh.name = "container";
            mesh.receiveShadow = true;
            mesh.castShadow = false; 
            mainGroup.add(mesh);
        }

        // Flower modelling code
        function generateFlower(id, colorHex) {
            const config = FLOWERS.find(f => f.id === id);
            const group = new THREE.Group();
            
            group.userData = { id: id, name: config.name, color: colorHex };

            const stemLen = 1.0;
            const stemMat = getRealisticMat(0x228B22, 0.8, 0.0, 0, 0).clone(); 
            const stem = new THREE.Mesh(new THREE.CylinderGeometry(0.015, 0.02, stemLen, 8), stemMat); 
            stem.position.y = -stemLen/2;
            stem.castShadow = true;
            stem.receiveShadow = true;
            stem.userData.isStem = true;
            group.add(stem);

            const centerColor = config.centerColor ? config.centerColor : 0xffdd00;
            const centerSize = config.centerSize || 0.15;
            
            const centerGeo = new THREE.TorusGeometry(centerSize * 0.8, centerSize * 0.2, 8, 16); 
            const center = new THREE.Mesh(centerGeo, getRealisticMat(centerColor, 0.9, 0.0, 0, 0).clone());
            center.castShadow = true;
            center.receiveShadow = true;
            group.add(center);

            const petalBaseMat = getRealisticMat(colorHex, 0.8, 0.0, 0.0, 0.0); 
            const count = config.petals;

            if (id === 'rose') {
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 4;
                    const radius = 0.05 + (i * 0.015) + (Math.random() * 0.02);
                    const y = (i * 0.01) + (Math.random() * 0.01);
                    const petalWidth = 0.2 + (i*0.01) + (Math.random() * 0.02);
                    const petalHeight = 0.3 + (i*0.02) + (Math.random() * 0.03);
                    
                    const petalGeo = createPetalShape(petalWidth, petalHeight, 10, 0.1 + (i/count)*0.05, 0.2);
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angle)*radius, y, Math.sin(angle)*radius);
                    petal.rotation.order = 'YXZ';
                    petal.rotation.y = angle + Math.PI/2 + (Math.random()-0.5) * 0.3; 
                    petal.rotation.x = -Math.PI/4 + (i / count) * Math.PI/3 + (Math.random()-0.5) * 0.1;
                    petal.rotation.z = (Math.random()-0.5) * 0.2; 
                    
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'tulip') {
                center.visible = false; 

                const petalGeo = createPetalShape(0.3, 0.8, 10, 0.2, 0.8);
                // Outer layer (3 petals)
                for(let i=0; i<3; i++) {
                    const angle = (i / 3) * Math.PI * 2;
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*0.12, 0.05, Math.sin(angle)*0.12);
                    petal.rotation.y = angle;
                    petal.rotation.x = -Math.PI/2 + 0.45 + (Math.random()-0.5) * 0.1; 
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
                // Inner layer (3 petals, offset angle)
                for(let i=0; i<3; i++) {
                    const angle = (i / 3) * Math.PI * 2 + Math.PI/3; 
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*0.08, 0.1, Math.sin(angle)*0.08);
                    petal.rotation.y = angle;
                    petal.rotation.x = -Math.PI/2 + 0.3 + (Math.random()-0.5) * 0.1; 
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'daisy') {
                center.geometry = new THREE.SphereGeometry(centerSize * 0.8, 16, 16); 
                center.material = getRealisticMat(centerColor, 0.9, 0.0).clone();

                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 2;
                    const petalGeo = createPetalShape(0.12, 0.65, 8, 0.05, 0.2); // Less pointy tip
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*0.25, 0, Math.sin(angle)*0.25);
                    petal.rotation.z = Math.PI/2; // Lay flat
                    petal.rotation.y = angle;
                    petal.rotation.x = (Math.random()-0.5) * 0.05;
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'sunflower') {
                // Adjust center for sunflower
                center.geometry = new THREE.SphereGeometry(centerSize * 0.8, 32, 32); // Use Sphere
                center.material = getRealisticMat(0x6e2c00, 0.9, 0.0).clone();
                center.position.y = 0.05; // Lift the center up
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 2 + (Math.random() - 0.5) * 0.1;
                    const radius = 0.35 + (Math.random() * 0.05);
                    const petalGeo = createPetalShape(0.3, 0.7, 8, 0.15, 0.6); // Wider, vibrant petals
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*radius, 0, Math.sin(angle)*radius);
                    petal.rotation.z = Math.PI/2;
                    petal.rotation.y = angle + Math.PI/2;
                    petal.rotation.x = (Math.random()-0.5) * 0.1;
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'lily') {
                center.geometry = new THREE.SphereGeometry(centerSize * 0.2, 8, 8); // Small, subtle center
                center.material = getRealisticMat(centerColor, 0.9, 0.0);
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 2;
                    // Longer, thinner, and extremely pointy petal
                    const petalGeo = createPetalShape(0.2, 1.2, 10, 0.05, 1.0); 
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*0.1, 0.1, Math.sin(angle)*0.1);
                    petal.rotation.y = angle;
                    // Curl back significantly
                    petal.rotation.x = -Math.PI/2 + 1.2 + (Math.random()-0.5) * 0.2; 
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'orchid') {
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 2;
                    const petalGeo = createPetalShape(0.4, 0.4, 12, 0.2, 0.5); // Wide, flat petals
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*0.1, 0.0, Math.sin(angle)*0.1);
                    petal.rotation.y = angle;
                    petal.rotation.x = -Math.PI/2 + 0.1;
                    petal.scale.y = (i < 3 ? 1.0 : 0.8 + Math.random() * 0.2); // Slipper petal is implied by scale variance
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
                // Lip/Throat
                const lipGeo = new THREE.CylinderGeometry(0.1, 0.05, 0.2, 16);
                const lip = new THREE.Mesh(lipGeo, getRealisticMat(0xff99cc, 0.3, 0.05));
                lip.position.y = 0.05;
                lip.rotation.x = Math.PI / 2;
                group.add(lip);
            } else if (id === 'hydrangea') {
                center.visible = false;
                for (let i = 0; i < 20; i++) { // Generate clusters of small flowers
                    const subFlowerGroup = new THREE.Group();
                    const rx = (Math.random() - 0.5) * 0.6;
                    const ry = (Math.random() - 0.5) * 0.6;
                    const rz = (Math.random() - 0.5) * 0.6;

                    for (let j = 0; j < 4; j++) { // 4 petals per sub-flower
                        const angle = (j / 4) * Math.PI * 2;
                        const petalGeo = createPetalShape(0.1, 0.15, 6, 0.02, 0.5);
                        const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                        petal.position.set(Math.cos(angle) * 0.05, 0, Math.sin(angle) * 0.05);
                        petal.rotation.z = Math.PI / 2;
                        petal.rotation.y = angle;
                        subFlowerGroup.add(petal);
                    }
                    subFlowerGroup.position.set(rx, ry + 0.3, rz);
                    subFlowerGroup.rotation.x = Math.random() * Math.PI;
                    subFlowerGroup.rotation.y = Math.random() * Math.PI;
                    group.add(subFlowerGroup);
                }
            } else if (id === 'peony') {
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 4;
                    const radius = 0.05 + (i * 0.01) + (Math.random() * 0.01);
                    const y = (i * 0.005) + (Math.random() * 0.005);
                    const petalWidth = 0.3 + (Math.random() * 0.05);
                    const petalHeight = 0.3 + (Math.random() * 0.05);
                    
                    const petalGeo = createPetalShape(petalWidth, petalHeight, 10, 0.1, 0.1); // Rounded, soft petals
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angle)*radius, y, Math.sin(angle)*radius);
                    petal.rotation.order = 'YXZ';
                    petal.rotation.y = angle + Math.PI/2 + (Math.random()-0.5) * 0.5; 
                    petal.rotation.x = -Math.PI/3 + (i / count) * Math.PI/3 + (Math.random()-0.5) * 0.2;
                    
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'poppy') {
                center.geometry = new THREE.SphereGeometry(centerSize * 1.5, 16, 16);
                center.material = getRealisticMat(0x2c3e50, 0.5, 0.1); // Dark, prominent center
                
                const poppyCount = 4; // Set to 4 for classic poppy
                for(let i=0; i<poppyCount; i++) {
                    const angle = (i / poppyCount) * Math.PI * 2;
                    // Significantly wider petal for thin, overlapping look
                    const petalGeo = createPetalShape(0.6, 0.6, 12, 0.3, 0.1); 
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angle)*0.2, 0.0, Math.sin(angle)*0.2); 
                    petal.rotation.y = angle;
                    // Very flat, almost horizontal
                    petal.rotation.x = -Math.PI/2 + 0.1 + (Math.random()-0.5) * 0.1; 
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'daffodil') {
                // Outer petals (6)
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 2;
                    const petalGeo = createPetalShape(0.2, 0.8, 10, 0.1, 0.8);
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*0.1, 0.0, Math.sin(angle)*0.1);
                    petal.rotation.y = angle + (Math.random() - 0.5) * 0.1; 
                    petal.rotation.x = -Math.PI/2 + 0.1 + (Math.random() - 0.5) * 0.1; 
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }

                const points = [];
                points.push(new THREE.Vector2(0.005, -0.1)); 
                points.push(new THREE.Vector2(0.1, 0.0));   
                points.push(new THREE.Vector2(0.15, 0.1));  
                points.push(new THREE.Vector2(0.12, 0.125)); 
                points.push(new THREE.Vector2(0.2, 0.3));

                const trumpetGeo = new THREE.LatheGeometry(points, 32); 

                const pos = trumpetGeo.attributes.position;
                const tempVector = new THREE.Vector3();

                for (let i = 0; i < pos.count; i++) {
                    tempVector.fromBufferAttribute(pos, i);
                    
                    if (tempVector.y > 0.05) {
                        const angle = Math.atan2(tempVector.z, tempVector.x);
                        
                        const wave = Math.sin(angle * 8) * 0.015; 
                        
                        const radius = tempVector.x * tempVector.x + tempVector.z * tempVector.z;
                        const factor = 1 + wave / Math.sqrt(radius);

                        pos.setX(i, tempVector.x * factor);
                        pos.setZ(i, tempVector.z * factor);
                    }
                }
                pos.needsUpdate = true;
                trumpetGeo.computeVertexNormals();

                const trumpetMat = getRealisticMat(config.centerColor, 0.3, 0.05, 0.05, 0).clone();
                const trumpet = new THREE.Mesh(trumpetGeo, trumpetMat);

                trumpet.rotation.x = Math.PI / 2; 
                trumpet.position.y = 0.0; 
                    
                center.visible = false; 
                group.add(trumpet);
            } else if (id === 'carnation') {
                center.visible = false; // Tightly packed petals
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 8; // Spiral of petals
                    const radius = 0.05 + (i * 0.005) + (Math.random() * 0.01);
                    const y = (i * 0.005) + (Math.random() * 0.005);

                    // Use a slightly wavy shape for ruffles
                    const petalGeo = createPetalShape(0.2, 0.25, 8, 0.05, 0.1);
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angle)*radius, y, Math.sin(angle)*radius);
                    petal.rotation.order = 'YXZ';
                    petal.rotation.y = angle + Math.PI/2 + (Math.random()-0.5) * 0.4; 
                    petal.rotation.x = -Math.PI/2 + 0.1 + (Math.random()-0.5) * 0.4;
                    
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'lavender') {
                center.visible = false; // No large center, many tiny flowers
                group.remove(stem); // Remove default stem
                
                const lavenderStemMat = getRealisticMat(0x4CAF50, 0.8).clone(); 
                const mainStem = new THREE.Mesh(new THREE.CylinderGeometry(0.01, 0.01, 2.0, 8), lavenderStemMat);
                
                mainStem.position.y = -1.0;
                mainStem.castShadow = true;
                mainStem.userData.isStem = true; // Identifier for easy retrieval
                group.add(mainStem);

                for(let i=0; i<count; i++) {
                    const flowerGroup = new THREE.Group();
                    const yPos = (i / count) * 1.5 - 0.75;
                    const angle = Math.random() * Math.PI * 2;
                    const radius = 0.05 + Math.random() * 0.05;

                    for (let j = 0; j < 4; j++) { // Tiny 4-petal flower
                        const pAngle = (j / 4) * Math.PI * 2;
                        const petalGeo = createPetalShape(0.05, 0.08, 4, 0.01, 0.5);
                        const petal = new THREE.Mesh(petalGeo, getRealisticMat(0x9b59b6, 0.5, 0.0).clone());
                        petal.position.set(Math.cos(pAngle) * 0.02, 0, Math.sin(pAngle) * 0.02);
                        petal.rotation.z = Math.PI / 2;
                        petal.rotation.y = pAngle;
                        flowerGroup.add(petal);
                    }
                    flowerGroup.position.set(Math.cos(angle)*radius, yPos, Math.sin(angle)*radius);
                    flowerGroup.rotation.x = Math.random() * 0.5;
                    flowerGroup.rotation.y = Math.random() * 0.5;
                    group.add(flowerGroup);
                }
            } else if (id === 'iris') {
                const innerMat = getRealisticMat(colorHex, 0.3, 0.05, 0.1, 0.5);
                const outerMat = getRealisticMat(colorHex, 0.5, 0.05, 0.0, 0.5); // Slightly different texture
                
                // 3 Standard petals (pointing up)
                for(let i=0; i<3; i++) {
                    const angle = (i / 3) * Math.PI * 2;
                    const petalGeo = createPetalShape(0.3, 0.7, 10, 0.1, 0.8);
                    const petal = new THREE.Mesh(petalGeo, innerMat.clone());
                    petal.position.set(Math.cos(angle)*0.1, 0.0, Math.sin(angle)*0.1);
                    petal.rotation.y = angle;
                    petal.rotation.x = -Math.PI/2 + 0.3 + (Math.random()-0.5) * 0.2; 
                    group.add(petal);
                }

                // 3 Fall petals (pointing down/out)
                for(let i=0; i<3; i++) {
                    const angle = (i / 3) * Math.PI * 2 + Math.PI/3;
                    const petalGeo = createPetalShape(0.35, 0.8, 10, 0.15, 0.1);
                    const petal = new THREE.Mesh(petalGeo, outerMat.clone());
                    petal.position.set(Math.cos(angle)*0.1, 0.0, Math.sin(angle)*0.1);
                    petal.rotation.y = angle;
                    petal.rotation.x = -Math.PI/2 - 0.2 + (Math.random()-0.5) * 0.1; 
                    group.add(petal);
                }
                center.visible = false;
            } else if (id === 'pansy') {
                center.geometry = new THREE.SphereGeometry(centerSize * 1.5, 8, 8);
                // 5 irregular petals
                const angles = [0, 1.5, 3.5, 5.0, 6.0]; 
                const scales = [1.2, 1.0, 1.0, 0.8, 0.8]; 
                
                const upperRotationX = -Math.PI/2 + 0.3; 
                const lowerRotationX = -Math.PI/2 - 0.2; 
                
                for(let i=0; i<count; i++) {
                    // Petals are wide and rounded (low tipPointy)
                    const petalGeo = createPetalShape(0.3 * scales[i], 0.3 * scales[i], 12, 0.15, 0.3); 
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angles[i])*0.05, 0.0, Math.sin(angles[i])*0.05);
                    petal.rotation.y = angles[i] + Math.PI/2;
                    
                    if (i < 2) { 
                        petal.rotation.x = upperRotationX;
                    } else { 
                        petal.rotation.x = lowerRotationX; 
                    }
                    
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
                center.position.y = -0.05; 
            } else if (id === 'marigold') {
                center.geometry = new THREE.SphereGeometry(centerSize * 1.2, 16, 16);
                center.material = getRealisticMat(0xd35400, 0.8, 0.0);
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 8; 
                    const radius = 0.05 + (i * 0.005) + (Math.random() * 0.005);
                    const y = (i * 0.01) + (Math.random() * 0.005);

                    const petalGeo = createPetalShape(0.15, 0.2, 6, 0.05, 0.1); // Small, compact, ruffled
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angle)*radius, y, Math.sin(angle)*radius);
                    petal.rotation.order = 'YXZ';
                    petal.rotation.y = angle + Math.PI/2 + (Math.random()-0.5) * 0.4; 
                    petal.rotation.x = -Math.PI/2 + 0.3 + (Math.random()-0.5) * 0.3;
                    
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'zinnia') {
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 3;
                    const radius = 0.05 + (i * 0.015);
                    const y = (i * 0.01);
                    
                    const petalGeo = createPetalShape(0.2, 0.4, 8, 0.05, 0.8);
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angle)*radius, y, Math.sin(angle)*radius);
                    petal.rotation.order = 'YXZ';
                    petal.rotation.y = angle + Math.PI/2 + (Math.random()-0.5) * 0.1; 
                    petal.rotation.x = -Math.PI/2 + 0.1; 
                    
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'cosmos') {
                center.geometry = new THREE.TorusGeometry(centerSize * 0.5, centerSize * 0.3, 8, 16);
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 2;
                    const petalGeo = createPetalShape(0.2, 0.6, 8, 0.05, 0.8);
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*0.2, 0, Math.sin(angle)*0.2);
                    petal.rotation.z = Math.PI/2;
                    petal.rotation.y = angle;
                    petal.rotation.x = (Math.random()-0.5) * 0.05;
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'dahlia') {
                center.visible = false;
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 6; // Dense spiral
                    const radius = 0.05 + (i * 0.005);
                    const y = (i * 0.015);
                    
                    const petalGeo = createPetalShape(0.1, 0.3, 8, 0.01, 1.0); // Narrow, sharp petals
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angle)*radius, y, Math.sin(angle)*radius);
                    petal.rotation.order = 'YXZ';
                    petal.rotation.y = angle + Math.PI/2 + (Math.random()-0.5) * 0.1; 
                    petal.rotation.x = -Math.PI/2 + 0.2 + (Math.random()-0.5) * 0.1;
                    
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'lotus') {
                // Center: Iconic seed pod (replaces default center geometry)
                center.geometry = new THREE.CylinderGeometry(0.1, 0.05, 0.2, 16);
                center.material = getRealisticMat(0x4CAF50, 0.9, 0.0); // Greenish seed head
                center.position.y = 0.1; 
                center.rotation.x = Math.PI / 2;
                
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 2;
                    const radius = 0.05 + (i * 0.02);
                    const y = (i * 0.05);

                    const petalGeo = createPetalShape(0.3, 0.7, 10, 0.1, 1.0); // Pointy, cupped
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    
                    petal.position.set(Math.cos(angle)*radius, y, Math.sin(angle)*radius);
                    petal.rotation.order = 'YXZ';
                    petal.rotation.y = angle + Math.PI/2; 
                    petal.rotation.x = -Math.PI/2 + 0.4 - (i/count)*0.4; // Open outwards as layers progress
                    
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            } else if (id === 'anemone') {
                center.geometry = new THREE.TorusGeometry(centerSize * 0.8, centerSize * 0.4, 16, 32); // Dark, dense center
                center.material = getRealisticMat(0x222222, 0.5, 0.0);
                
                // Single layer of wide petals
                for(let i=0; i<count; i++) {
                    const angle = (i / count) * Math.PI * 2;
                    const petalGeo = createPetalShape(0.3, 0.7, 10, 0.1, 0.5); 
                    const petal = new THREE.Mesh(petalGeo, petalBaseMat.clone());
                    petal.position.set(Math.cos(angle)*0.2, 0, Math.sin(angle)*0.2);
                    petal.rotation.z = Math.PI/2;
                    petal.rotation.y = angle;
                    petal.rotation.x = (Math.random()-0.5) * 0.05;
                    petal.castShadow = true;
                    petal.receiveShadow = true;
                    group.add(petal);
                }
            }

            // Add small green sepals/calyx below the flower head
            const sepalCount = 5;
            const sepalMat = getRealisticMat(0x388E3C, 0.6, 0.0, 0, 0).clone(); 
            for(let i=0; i<sepalCount; i++) {
                const angle = (i / sepalCount) * Math.PI * 2;
                const sepalGeo = new THREE.ConeGeometry(0.05, 0.1, 4);
                const sepal = new THREE.Mesh(sepalGeo, sepalMat);
                sepal.position.set(Math.cos(angle)*0.05, -0.05, Math.sin(angle)*0.05);
                sepal.rotation.x = Math.PI/2;
                sepal.rotation.z = angle;
                sepal.castShadow = true;
                sepal.userData.isCalyx = true;
                group.add(sepal);
            }

            const scaleFactor = 0.7; 
            group.scale.set(scaleFactor, scaleFactor, scaleFactor);
            
            return group;
        }

        // Used when the user selects the flower
        function highlightStem(flowerGroup, highlight) {
            if (!flowerGroup) return;
            // Find the stem mesh using the identifier added in generateFlower
            const stem = flowerGroup.children.find(c => c.userData.isStem);

            const calyxes = flowerGroup.children.filter(c => c.userData.isCalyx);
            
            if (stem && stem.material) {
                if (!stem.userData.originalColor) {
                    // Store original color once
                    stem.userData.originalColor = stem.material.color.getHex();
                }

                if (highlight) {
                    stem.material.color.setHex(0xff0000); // Red highlight
                } else {
                    // Revert to original color
                    stem.material.color.setHex(stem.userData.originalColor || 0x228B22); 
                }
            }
        }

        // Helper to find nearest flower to cursor (for tilting)
        function findNearestFlower(worldPos) {
            let nearest = null;
            let minDist = 0.5; // Threshold for selection

            mainGroup.children.forEach(child => {
                if(child.userData.name && child.name !== 'container' && child.visible) {
                    const childWorldPos = new THREE.Vector3();
                    child.getWorldPosition(childWorldPos);
                    const dist = worldPos.distanceTo(childWorldPos);
                    if(dist < minDist) {
                        minDist = dist;
                        nearest = child;
                    }
                }
            });
            return nearest;
        }

        // Flower generated when pinching hand gesture is detected
        function spawnFlowerAtCursor() {
            if(!state.handPosition) return;
            const flower = generateFlower(state.flowerId, state.color);
            
            const vector = new THREE.Vector3(state.handPosition.x, state.handPosition.y, 0.5);
            vector.unproject(camera);
            const dir = vector.sub(camera.position).normalize();
            const distance = 4;
            const pos = camera.position.clone().add(dir.multiplyScalar(distance));

            mainGroup.worldToLocal(pos);
            
            flower.position.copy(pos);
            flower.rotation.x = (Math.random() - 0.5) * 0.7;
            flower.rotation.z = (Math.random() - 0.5) * 0.7;

            mainGroup.add(flower);
        }

        // Reset arrangement
        function resetArrangement() {
            for(let i = mainGroup.children.length - 1; i >= 0; i--) {
                if(mainGroup.children[i].name !== 'container') {
                    mainGroup.remove(mainGroup.children[i]);
                }
            }
        }

        // Alert
        function showCustomAlert(type, message) {
            let iconHTML = '';

            if (type === "success"){
                iconHTML = "<i class='bi bi-check-circle'></i>";
            } else if (type === "danger"){
                iconHTML = "<i class='bi bi-exclamation-circle'></i>";
            } else if (type === "info"){
                iconHTML = "<i class='bi bi-info-circle'></i>";
            }

            const alertHTML = `
                <div class='alert alert-${type} alert-dismissible fade show px-4 py-1 position-fixed end-0 mt-3 mx-auto d-flex z-3' role='alert'>
                    <div class='pe-2'>
                        ${iconHTML}
                    </div>
                    ${message}
                </div>
            `;

            const container = document.createElement('div');
            container.innerHTML = alertHTML.trim();
            const alertElement = container.firstChild;
            
            document.body.appendChild(alertElement);

            setTimeout(() => {
                alertElement.classList.add('show');
            }, 10); 
            
            setTimeout(() => {
                alertElement.remove();
            }, 5000); 
        }
        
        // Take screenshot of the arrangement
        async function screenshotArrangement(flowerArrangementData) {
            const flowersInScene = [];
            mainGroup.children.forEach(child => {
                if(child.userData && child.userData.name) {
                    if(FLOWERS.some(f => f.name === child.userData.name)) {
                        flowersInScene.push(child.userData.name);
                    }
                }
            });
            
            if(flowersInScene.length === 0) {
                showCustomAlert('danger', 'Arrangement is empty. Please add flowers before saving.');
                return;
            }

            const uniqueFlowers = [...new Set(flowersInScene)];
            
            const arrangementData = {
                date: new Date().toISOString(),
                flowerList: uniqueFlowers, 
                arrangementObjects: flowerArrangementData, 
            };

            const originalClearAlpha = renderer.getClearAlpha();

            document.getElementById('ui-sidebar').style.display = 'none';
            document.getElementById('video-feed').style.display = 'none';
            document.getElementById('gesture-hint').style.display = 'none';
            cursorMesh.visible = false;

            renderer.setClearAlpha(0);
            renderer.render(scene, camera);

            const canvas = renderer.domElement;
            
            const minDimension = Math.min(canvas.width, canvas.height);
            const fullDataURL = canvas.toDataURL('image/png');

            const img = new Image();
            img.src = fullDataURL;

            await new Promise(resolve => img.onload = resolve);
            
            // Create a new canvas to handle the crop (1:1 ratio)
            const croppedCanvas = document.createElement('canvas');
            croppedCanvas.width = minDimension;
            croppedCanvas.height = minDimension;
            const croppedContext = croppedCanvas.getContext('2d');
            
            const xOffset = (canvas.width - minDimension) / 2;
            const yOffset = (canvas.height - minDimension) / 2;

            // Draw the image onto the new canvas, cropped and centered
            croppedContext.drawImage(img, xOffset, yOffset, minDimension, minDimension, 0, 0, minDimension, minDimension);

            const imageBlob = await new Promise(resolve => {
                croppedCanvas.toBlob(resolve, 'image/png');
            });

            // Local Download
            const downloadLink = document.createElement('a');
            const url = URL.createObjectURL(imageBlob);
            
            downloadLink.href = url;
            downloadLink.download = 'ar-floral-arrangement-' + Date.now() + '.png';

            // Trigger the download
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            URL.revokeObjectURL(url);
            
            // Form submission to be saved in database
            const formData = new FormData();
            formData.append('screenshot', imageBlob, 'ar-arrangement.png'); 
            formData.append('arrangement_data', JSON.stringify(arrangementData));

            try {
                const response = await fetch('save_arrangement.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    showCustomAlert('success', 'Your beautiful arrangement has been saved successfully!');
                } else {
                    console.error("Server submission failed:", result.message);
                    showCustomAlert('danger', "Error saving arrangement.");
                }
            } catch (error) {
                console.error("Network or critical failure:", error);
                showCustomAlert('danger', "Network error. Could not connect to the server or process response.");
            }
            
            renderer.setClearAlpha(originalClearAlpha);
            
            document.getElementById('ui-sidebar').style.display = 'block'; 
            document.getElementById('video-feed').style.display = 'block';
            document.getElementById('gesture-hint').style.display = 'block';
            cursorMesh.visible = true;
        }

        // Flower buttons logic
        const flowerGrid = document.getElementById('flower-grid');
        FLOWERS.forEach(f => {
            const col = document.createElement('div');
            col.className = 'col'; // Bootstrap column
            
            const btn = document.createElement('div');
            // Adjust class logic to work with light background styles
            btn.className = `flower-btn p-2 rounded text-center small cursor-pointer text-light ${f.id === state.flowerId ? 'bg-br1' : 'bg-br2'}`;
            btn.id = `btn-${f.id}`;
            btn.innerText = f.name;
            btn.onclick = () => {
                document.querySelectorAll('.flower-btn').forEach(b => {
                    b.classList.remove('bg-br1');
                    b.classList.add('bg-br2');
                });
                btn.classList.add('bg-br1');
                btn.classList.remove('bg-br2');
                state.flowerId = f.id;
            };
            
            col.appendChild(btn);
            flowerGrid.appendChild(col);
        });

        // Color buttons logic
        const colorPalette = document.getElementById('color-palette');
        COLORS.forEach(c => {
            const dot = document.createElement('div');
            dot.className = `color-dot ${c === state.color ? 'active' : ''}`;
            dot.style.backgroundColor = c;
            dot.onclick = () => {
                document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
                dot.classList.add('active');
                state.color = c;
            };
            colorPalette.appendChild(dot);
        });

        // Container option listener
        document.getElementById('container-select').addEventListener('change', (e) => {
            createContainer(e.target.value);
        });

        createContainer('pot');
        updateCameraAndScene();

        // Zoom using mouse wheel
        renderer.domElement.addEventListener('wheel', (e) => {
            e.preventDefault();
            
            cameraControls.distance += e.deltaY * ZOOM_SENSITIVITY * cameraControls.distance;
            
            cameraControls.distance = THREE.MathUtils.clamp(cameraControls.distance, 1.5, 10);

            updateCameraAndScene();
        });

        // GEMINI API FUNCTIONS
        async function geminiCall(prompt, systemInstruction = "", model = "gemini-2.5-flash-preview-09-2025", useGrounding = true) {
            const outputDiv = document.getElementById('ai-output');
            outputDiv.classList.remove('d-none');
            outputDiv.innerHTML = "<span class='typing-indicator fw-semibold'>Consulting the Florist</span>";

            try {
                const MAX_RETRIES = 3;
                let attempts = 0;
                let response;

                while (attempts < MAX_RETRIES) {
                    try {
                        const tools = useGrounding ? [{ "google_search": {} }] : undefined;
                        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;
                        
                        response = await fetch(apiUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                contents: [{ parts: [{ text: prompt }] }],
                                systemInstruction: { parts: [{ text: systemInstruction }] },
                                tools: tools
                            })
                        });

                        if (response.ok) break;

                        // Exponential backoff
                        if (attempts < MAX_RETRIES - 1) {
                            const delay = Math.pow(2, attempts) * 1000;
                            await new Promise(resolve => setTimeout(resolve, delay));
                        }
                        attempts++;

                    } catch (error) {
                        if (attempts < MAX_RETRIES - 1) {
                             const delay = Math.pow(2, attempts) * 1000;
                            await new Promise(resolve => setTimeout(resolve, delay));
                        }
                        attempts++;
                        if (attempts === MAX_RETRIES) throw error; 
                    }
                }
                
                if (!response.ok) throw new Error("API Error: " + response.statusText);

                const data = await response.json();
                const text = data.candidates?.[0]?.content?.parts?.[0]?.text || "The florist is silent.";
                return text;

            } catch (error) {
                console.error("Gemini API Call Failed:", error);
                outputDiv.innerText = "Error consulting AI. Check console for details.";
                return null;
            }
        }

        // Suggestion for 3 flowers based on occasion
        async function askGeminiSuggestion() {
            const occasion = document.getElementById('ai-prompt').value;
            const outputDiv = document.getElementById('ai-output');

            if(!occasion) {
                outputDiv.classList.remove('d-none');
                outputDiv.innerHTML = "<div class='fw-bold text-danger'>Error:</div> Please enter an occasion first!";
                return;
            }

            const flowerNames = FLOWERS.map(f => f.name).join(', ');
            
            const systemPrompt = `You are a master florist. The user will provide an occasion or mood. You must suggest 3 distinct flowers from this specific list: [${flowerNames}]. 
            Return the response in strict JSON format: { "suggestions": [{"flower": "FlowerName", "reason": "Short reason"}] }. Do not include Markdown blocks.`;

            const prompt = `Occasion: ${occasion}`;
            
            const responseText = await geminiCall(prompt, systemPrompt);
            
            if(responseText) {
                try {
                    const cleanJson = responseText.replace(/```json/g, '').replace(/```/g, '').trim();
                    const result = JSON.parse(cleanJson);
                    
                    let html = `<div class="fw-bold suggestion pb-1">Suggestions:</div>`;
                    result.suggestions.forEach(s => {
                        html += `<div class="mb-2"><span class="fw-bold">${s.flower}</span>: <span class="small opacity-75">${s.reason}</span></div>`;
                        const flowerId = FLOWERS.find(f => f.name === s.flower)?.id;
                        if(flowerId) {
                            const btn = document.getElementById(`btn-${flowerId}`);
                            if(btn) {
                                btn.style.borderColor = '#a78bfa';
                                setTimeout(() => btn.style.borderColor = 'rgba(0,0,0,0.1)', 5000);
                            }
                        }
                    });
                    outputDiv.innerHTML = html;
                } catch(e) {
                    outputDiv.innerHTML = responseText;
                }
            }
        }

        // Gemini analyse ur flower arrangement
        async function askGeminiAnalysis() {
            const flowersInScene = [];
            mainGroup.children.forEach(child => {
                if(child.userData && child.userData.name) {
                    if(FLOWERS.some(f => f.name === child.userData.name)) {
                        flowersInScene.push(child.userData.name);
                    }
                }
            });
            const outputDiv = document.getElementById('ai-output');

            if(flowersInScene.length === 0) {
                outputDiv.classList.remove('d-none');
                outputDiv.innerHTML = "<div class='fw-bold text-danger'>Error:</div> Please arrange some flowers first!";
                return;
            }

            const uniqueFlowers = [...new Set(flowersInScene)].join(', ');
            
            const systemPrompt = `You are an expert on floriography (the language of flowers). Analyze the user's arrangement and give a poetic, brief interpretation of its meaning. Keep it under 50 words.`;
            const prompt = `My arrangement contains: ${uniqueFlowers}. What does this mean symbolically?`;

            const text = await geminiCall(prompt, systemPrompt);
            if(text) {
                outputDiv.innerHTML = `<div class="fw-bold suggestion">Meaning:</div><div id="interpretation-text" class="fst-italic small">"${text}"</div>`;
            }
        }

        // 5. MEDIAPIPE & GESTURE LOGIC 
        const videoElement = document.getElementById('video-feed');
        const loadingBar = document.getElementById('loading-bar');
        const loadingScreen = document.getElementById('loading-screen');
        const statusMsg = document.getElementById('status-msg');

        let pinchCooldown = false;

        function onResults(results) {
            if(loadingScreen.style.display !== 'none') {
                loadingScreen.style.opacity = 0;
                loadingScreen.style.pointerEvents = 'none'; 
                setTimeout(() => loadingScreen.style.display = 'none', 500);
            }
            
            const isHandDetected = results.multiHandLandmarks && results.multiHandLandmarks.length > 0;
            let currentIsPinching = false;
            let currentIsFist = false;

            if (isHandDetected) {
                const landmarks = results.multiHandLandmarks[0];
                
                const indexTip = landmarks[8];
                const thumbTip = landmarks[4];
                const wrist = landmarks[0];
                
                const pinchDist = Math.hypot(indexTip.x - thumbTip.x, indexTip.y - thumbTip.y);
                currentIsPinching = pinchDist < 0.05;

                const tipAvgDist = (
                    Math.hypot(landmarks[12].x - wrist.x, landmarks[12].y - wrist.y) +
                    Math.hypot(landmarks[16].x - wrist.x, landmarks[16].y - wrist.y) +
                    Math.hypot(landmarks[20].x - wrist.x, landmarks[20].y - wrist.y) 
                ) / 3;
                currentIsFist = tipAvgDist < 0.35; // A more robust fist check

                const x = (1 - indexTip.x) * 2 - 1; 
                const y = -(indexTip.y * 2 - 1);
                
                state.handPosition.set(x, y, 0);
                
                // Calculate 3D Cursor Position
                const vector = new THREE.Vector3(x, y, 0.5);
                vector.unproject(camera);
                const dir = vector.sub(camera.position).normalize();
                const distance = cameraControls.distance; // Use current zoomed distance
                const cursorPos = camera.position.clone().add(dir.multiplyScalar(distance));
                
                cursorMesh.position.copy(cursorPos);
            }
            
            // Handle cleanup/reversion from previous frame
            if (!currentIsPinching && !currentIsFist) {
                if (state.highlightedObject) {
                    highlightStem(state.highlightedObject, false);
                    state.highlightedObject = null;
                }
                
                if (state.draggedObject) {
                    const localPos = state.draggedObject.position;
                    if (localPos.length() > 2.5) {
                        mainGroup.remove(state.draggedObject);
                        statusMsg.innerText = "🗑️ DELETED";
                    } else {
                        statusMsg.innerText = "✋ DROPPED";
                        // Re-generate to ensure clean materials after highlight/delete check
                        const data = state.draggedObject.userData;
                        const currentRotation = state.draggedObject.rotation.clone();
                        mainGroup.remove(state.draggedObject);
                        
                        const newFlower = generateFlower(data.id, data.color);
                        newFlower.position.copy(localPos);
                        newFlower.rotation.copy(currentRotation);
                        
                        newFlower.rotation.x += (Math.random() - 0.5) * 0.2;
                        newFlower.rotation.z += (Math.random() - 0.5) * 0.2;
                        
                        mainGroup.add(newFlower);
                    }
                    state.draggedObject = null;
                    statusMsg.classList.remove('text-danger');
                }
            }
            
            state.isPinching = currentIsPinching;
            state.isFist = currentIsFist;
            
            if (state.isFist) {
                cursorMesh.material.color.setHex(0xff0000);
                
                let target = findNearestFlower(cursorMesh.position);
                
                if (target) {
                    // Highlight the new target stem if it's different from the old one
                    if (state.highlightedObject !== target) {
                        if (state.highlightedObject) highlightStem(state.highlightedObject, false);
                        highlightStem(target, true);
                        state.highlightedObject = target;
                    }
                    
                    statusMsg.innerText = "✊ TILTING FLOWER";
                    
                    // Simple tilt based on cursor position
                    const normalizedX = (cursorMesh.position.x - mainGroup.position.x) / (cameraControls.distance * 0.5);
                    const normalizedY = (cursorMesh.position.y - mainGroup.position.y) / (cameraControls.distance * 0.5);
                    
                    target.rotation.x = THREE.MathUtils.clamp(normalizedY * 0.7, -Math.PI / 3, Math.PI / 3);
                    target.rotation.z = THREE.MathUtils.clamp(normalizedX * 0.7, -Math.PI / 3, Math.PI / 3);
                    
                } else {
                    // If fist is active but no flower is near, clear highlight
                    if (state.highlightedObject) {
                        highlightStem(state.highlightedObject, false);
                        state.highlightedObject = null;
                    }
                    statusMsg.innerText = "✊ ROTATING CONTAINER (360°)";
                    
                    // Rotate container based on index tip movement (x, y)
                    const x = state.handPosition.x;
                    const y = state.handPosition.y;
                    
                    mainGroup.rotation.y += (x * 0.02); 
                    mainGroup.rotation.x += (y * 0.005); 
                    const maxRotation = Math.PI / 3; 
                    mainGroup.rotation.x = THREE.MathUtils.clamp(mainGroup.rotation.x, -maxRotation, maxRotation);
                }
                
                state.draggedObject = null; 
                
            } else if (state.isPinching) {
                cursorMesh.material.color.setHex(0xffff00);

                if (state.draggedObject) {
                    // Dragging
                    statusMsg.innerText = "👌 DRAGGING FLOWER";
                    
                    const localPos = cursorMesh.position.clone();
                    mainGroup.worldToLocal(localPos);
                    state.draggedObject.position.copy(localPos);

                    if (localPos.length() > 2.5) {
                        statusMsg.innerText = "🗑️ RELEASE TO DELETE";
                        statusMsg.classList.add('text-danger');
                        // Change flower head to red for delete visual cue
                        state.draggedObject.traverse(child => {
                            if(child.isMesh && child.material && !child.userData.isStem && !child.userData.isCalyx) {
                                child.material.color.setHex(0xff0000);
                            }
                        });
                    } else {
                        statusMsg.classList.remove('text-danger');
                        // Revert flower head color if out of delete zone
                         state.draggedObject.traverse(child => {
                            if(child.isMesh && child.material && child.userData.color && !child.userData.isStem && !child.userData.isCalyx) {
                                child.material.color.setHex(parseInt(child.userData.color.replace('#', '0x')));
                            }
                        });
                    }
                } 
                else if (!pinchCooldown) {
                    // Grab or Place
                    const nearest = findNearestFlower(cursorMesh.position);
                    
                    if (nearest) {
                        state.draggedObject = nearest;
                        
                        // Highlight stem upon successful grab
                        if (state.highlightedObject) highlightStem(state.highlightedObject, false);
                        highlightStem(state.draggedObject, true);
                        state.highlightedObject = state.draggedObject;
                        
                        statusMsg.innerText = "👌 GRABBED";
                    } else {
                        spawnFlowerAtCursor();
                        statusMsg.innerText = "👌 PLACED FLOWER";
                        pinchCooldown = true;
                        setTimeout(() => { pinchCooldown = false; }, 500);
                    }
                }

            } else if (isHandDetected) {
                // No specific gesture but hands are visible
                statusMsg.innerText = "✋ Hand Detected";
                cursorMesh.material.color.setHex(0x00ff00);
            } else {
                // No hand detected
                statusMsg.innerText = "No Hand Detected";
                state.draggedObject = null;
                cursorMesh.material.color.setHex(0x00ff00);
            }
        }

        const hands = new Hands({locateFile: (file) => {
            return `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`;
        }});

        hands.setOptions({
            maxNumHands: 1,
            modelComplexity: 1,
            minDetectionConfidence: 0.5,
            minTrackingConfidence: 0.5
        });

        hands.onResults(onResults);

        const cameraUtils = new Camera(videoElement, {
            onFrame: async () => {
                await hands.send({image: videoElement});
            },
            width: 640,
            height: 480
        });

        // Start Camera
        loadingBar.style.width = "50%";
        cameraUtils.start()
            .then(() => {
                loadingBar.style.width = "100%";
            })
            .catch(err => {
                console.error(err);
                statusMsg.innerText = "Camera access denied or failed.";
            });

        // ANIMATION LOOP
        function animate() {
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
        }
        animate();

        // Handle Window Resize
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    </script>
</body>
</html>