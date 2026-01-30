// Three.js 3D Scene for SportsDoor Hero
(function() {
    'use strict';
    
    const canvas = document.getElementById('sd-3d-canvas');
    if (!canvas) return;
    
    let scene, camera, renderer, ring, ball, particles;
    let mouseX = 0, mouseY = 0;
    let windowHalfX = window.innerWidth / 2;
    let windowHalfY = window.innerHeight / 2;
    
    function init() {
        // Scene setup
        scene = new THREE.Scene();
        
        // Camera - wait for container to be ready
        const container = canvas.parentElement;
        if (!container) {
            setTimeout(init, 100);
            return;
        }
        
        const width = Math.max(container.clientWidth || 500, 400);
        const height = Math.max(container.clientHeight || 500, 400);
        
        camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
        camera.position.z = 8;
        camera.position.y = 0;
        camera.position.x = 0;
        
        // Renderer
        renderer = new THREE.WebGLRenderer({ 
            canvas: canvas,
            alpha: true,
            antialias: true,
            powerPreference: "high-performance"
        });
        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setClearColor(0x000000, 0); // Transparent background
        
        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
        scene.add(ambientLight);
        
        const pointLight1 = new THREE.PointLight(0x3b82f6, 1, 100);
        pointLight1.position.set(5, 5, 5);
        scene.add(pointLight1);
        
        const pointLight2 = new THREE.PointLight(0x8b5cf6, 0.8, 100);
        pointLight2.position.set(-5, -5, 5);
        scene.add(pointLight2);
        
        // Create rotating ring (torus)
        const ringGeometry = new THREE.TorusGeometry(2, 0.15, 16, 100);
        const ringMaterial = new THREE.MeshStandardMaterial({
            color: 0x3b82f6,
            emissive: 0x1e40af,
            emissiveIntensity: 0.3,
            metalness: 0.8,
            roughness: 0.2
        });
        ring = new THREE.Mesh(ringGeometry, ringMaterial);
        ring.rotation.x = Math.PI / 2;
        scene.add(ring);
        
        // Create inner ball/sphere
        const ballGeometry = new THREE.SphereGeometry(0.6, 32, 32);
        const ballMaterial = new THREE.MeshStandardMaterial({
            color: 0x8b5cf6,
            emissive: 0x6d28d9,
            emissiveIntensity: 0.4,
            metalness: 0.9,
            roughness: 0.1
        });
        ball = new THREE.Mesh(ballGeometry, ballMaterial);
        scene.add(ball);
        
        // Add particles for energy effect
        const particleGeometry = new THREE.BufferGeometry();
        const particleCount = 100;
        const positions = new Float32Array(particleCount * 3);
        
        for (let i = 0; i < particleCount * 3; i += 3) {
            const radius = 3 + Math.random() * 2;
            const theta = Math.random() * Math.PI * 2;
            const phi = Math.random() * Math.PI;
            
            positions[i] = radius * Math.sin(phi) * Math.cos(theta);
            positions[i + 1] = radius * Math.sin(phi) * Math.sin(theta);
            positions[i + 2] = radius * Math.cos(phi);
        }
        
        particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        
        const particleMaterial = new THREE.PointsMaterial({
            color: 0x60a5fa,
            size: 0.05,
            transparent: true,
            opacity: 0.6
        });
        
        particles = new THREE.Points(particleGeometry, particleMaterial);
        scene.add(particles);
        
        // Mouse movement tracking for interactive rotation
        document.addEventListener('mousemove', onMouseMove, false);
        
        // Handle window resize
        window.addEventListener('resize', onWindowResize, false);
        
        // Start animation loop
        animate();
    }
    
    function onMouseMove(event) {
        mouseX = (event.clientX - windowHalfX) * 0.01;
        mouseY = (event.clientY - windowHalfY) * 0.01;
    }
    
    function onWindowResize() {
        const container = canvas.parentElement;
        if (!container || !camera || !renderer) return;
        
        const width = Math.max(container.clientWidth || 500, 400);
        const height = Math.max(container.clientHeight || 500, 400);
        
        windowHalfX = window.innerWidth / 2;
        windowHalfY = window.innerHeight / 2;
        
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    }
    
    function animate() {
        requestAnimationFrame(animate);
        
        // Rotate ring
        ring.rotation.z += 0.005;
        ring.rotation.y += 0.01;
        
        // Rotate ball
        ball.rotation.x += 0.01;
        ball.rotation.y += 0.015;
        
        // Rotate particles
        particles.rotation.y += 0.002;
        
        // Interactive camera movement based on mouse
        camera.position.x += (mouseX - camera.position.x) * 0.05;
        camera.position.y += (-mouseY - camera.position.y) * 0.05;
        camera.lookAt(scene.position);
        
        renderer.render(scene, camera);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
