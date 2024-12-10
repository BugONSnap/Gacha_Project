<script lang="ts">
    import { onMount } from 'svelte';
  
    let path: SVGPathElement;
    
    onMount(() => {
      const WIDTH = window.innerWidth;
      const HEIGHT = window.innerHeight;
      const WAVEHEIGHT = 60;
      const FREQUENCY = 200;
      const SPEED = 4;
  
      let xs: number[] = [];
      let tick = 0;
  
      function createWave() {
        xs = [];
        for (let i = 0; i <= WIDTH; i++) {
          xs.push(i);
        }
      }
  
      function animate() {
        let points = xs.map(x => {
          let y = HEIGHT / 2 + WAVEHEIGHT * Math.sin((x + tick) / FREQUENCY);
          return [x, y];
        });
  
        let pathData = "M" + points.map(p => `${p[0]},${p[1]}`).join(" L") +
          `L ${WIDTH},${HEIGHT} L 0,${HEIGHT} Z`;
  
        path?.setAttribute("d", pathData);
        tick += SPEED;
        requestAnimationFrame(animate);
      }
  
      createWave();
      animate();
  
      const handleResize = () => {
        createWave();
      };
  
      window.addEventListener("resize", handleResize);
      return () => window.removeEventListener("resize", handleResize);
    });
</script>
  
<div class="wave-container">
    <svg width="100%" height="100%">
        <defs>
            <pattern id="img1" patternUnits="userSpaceOnUse" width="100" height="1">
                <image 
                    href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAADICAIAAACRXtOWAAADQUlEQVR4nO3Uy3HrMBQEUb1cHADzX9EROKR5C7v8kWlpRiKJC6A7gltTB7is63qp18vLi+p1WZal9TIbrevaepmN/rWeZaNlWd7e3lpfcd3r62vrE7aqyariE1yWpfUsG9X82WFlB6sgWLnBKghWbrAKgpUbrIJg5QarIFi5wSoIVm6wCoKVG6yCYOUGqyBYucEqCFZusAqClRusgmDlBqsgWLnBKghWbrAKgpUbrIJg5QarIFi5wSoIVm6wCoKVG6yCYOUGqyBYucEqCFZusAqClRusgmDlBqsgWLnBKghWbrAKgpUbrIJg5QarIFi5wSoIVm6wCoKVG6yCYOUGqyBYucEqCFZusAqClRusgmDlBqsgWLnBKghWbrAKgpUbrIJg5QarIFi5wSoIVm6wCoKVG6yCYOUGqyBYucEqCFZusAqClRusgmAV1HqWjWqyWpal4lg1Wa3rWm6ssqwklRurLCtVG6syK1UbqzIrlRqrOCuVGqs4K9UZqz4r1RmrPisVGasLVioyVhesVGGsXlipwli9sFLzsTpipeZjdcRKbcfqi5XajtUXKzUcqztWajhWd6zUaqweWanVWD2yUpOxOmWlJmN1ykrnj9UvK50/Vr+sdPJYXbPSyWN1zUpnjtU7K505Vu+sdNpYA7DSaWMNwErnjDUGK50z1hisdMJYw7DSCWMNw0pHjzUSKx091kisdOhYg7HSoWMNxkrHjTUeKx031nisdNBYQ7LSQWMNyUpHjDUqKx0x1qisdPtYA7PS7mMNzEr7jjU2K+071tistONYw7PSjmMNz0p7jTUDK+011gystMtYk7DSLmNNwkrPjzUPKz0/1jys9ORYU7HSk2NNxUrPjDUbKz0z1mys9PBYE7LSw2NNyEqPjTUnKz021pys9MBY07LSA2NNy0rpWDOzUjrWzKwUjTU5K0VjTc5K/liwkj8WrGSOBav3rLFg9d79sWD12f2xYPXZnbFg9b07Y8Hqe7fGgtVVt8aC1VV/jgWr3/05Fqx+tz0WrDbbHgtWm22MBau/2hgLVn91PRasbnQ9Fqxu9GMsWN3ux1iwut3XWLC629dYsLrbx1iwcvoYC1ZOF8HK7iJY2f0HmTYOVR+4YhkAAAAASUVORK5CYII=" 
                    x="0" 
                    y="0" 
                    width="100" 
                    height="200"
                />
            </pattern>
        </defs>
        <path bind:this={path} fill="url(#img1)" />
    </svg>
</div>
  
<style>
    .wave-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
  
    svg {
        display: block;
    }
</style> 