let products = [];
let orders = [];
let customers = [];

const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value).replace('IDR', 'Rp');

const pageButtons = document.querySelectorAll('[data-page]');
const pages = document.querySelectorAll('.page');
const sidebar = document.getElementById('sidebar');

function showToast(message) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');
  window.setTimeout(() => toast.classList.remove('show'), 2300);
}

function setPage(pageName) {
  pageButtons.forEach(button => button.classList.toggle('active', button.dataset.page === pageName));
  pages.forEach(page => page.classList.toggle('active', page.id === `page-${pageName}`));
  sidebar.classList.remove('open');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

pageButtons.forEach(button => button.addEventListener('click', () => setPage(button.dataset.page)));
document.querySelectorAll('[data-page-target]').forEach(button => {
  button.addEventListener('click', () => setPage(button.dataset.pageTarget));
});

document.getElementById('mobileToggle').addEventListener('click', () => sidebar.classList.toggle('open'));

document.addEventListener('click', (event) => {
  if (window.innerWidth <= 920 && sidebar.classList.contains('open')) {
    const clickedInside = sidebar.contains(event.target) || event.target.id === 'mobileToggle';
    if (!clickedInside) sidebar.classList.remove('open');
  }
});

function renderBestProducts() {
  const target = document.getElementById('bestProducts');
  if (products.length === 0) {
      target.innerHTML = '<p>Belum ada produk</p>';
      return;
  }
  const best = [...products].sort((a, b) => b.terjual - a.terjual).slice(0, 2);
  target.innerHTML = best.map(item => `
    <div class="best-item">
      <img src="${item.image}" alt="${item.name}" onerror="this.src='../image/Logo no BG 1.png'" />
      <div><strong>${item.name}</strong><small>${item.terjual || 0} unit terjual</small></div>
      <b>${money(item.price)}</b>
    </div>
  `).join('');
}

function orderStatusClass(status) {
  return status.replace(/\s+/g, '');
}

function renderRecentOrders() {
  const target = document.getElementById('recentOrdersBody');
  const recent = orders.slice(0, 4);
  target.innerHTML = recent.map(order => `
    <tr>
      <td><strong>${order.id}</strong></td>
      <td>${customerMini(order.customer)}</td>
      <td>${order.date}</td>
      <td><span class="status ${orderStatusClass(order.status)}">${order.status}</span></td>
      <td class="right"><strong>${money(order.total)}</strong></td>
    </tr>
  `).join('') || `<tr><td colspan="5" style="text-align:center;">Belum ada pesanan</td></tr>`;
}

function customerMini(name) {
  const initials = name.split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase();
  return `<span style="display:inline-flex;align-items:center;gap:10px"><span class="mini-avatar">${initials}</span>${name}</span>`;
}

function renderOrders() {
  const tbody = document.getElementById('ordersTableBody');
  const search = document.getElementById('orderSearch').value.trim().toLowerCase();
  const status = document.getElementById('statusFilter').value;
  const payment = document.getElementById('paymentFilter').value;

  const filtered = orders.filter(order => {
    const matchesSearch = [order.id, order.customer, order.email, order.payment, order.status].some(value => value.toLowerCase().includes(search));
    const matchesStatus = status === 'all' || order.status === status;
    const matchesPayment = payment === 'All Payments' || order.payment.includes(payment);
    return matchesSearch && matchesStatus && matchesPayment;
  });

  tbody.innerHTML = filtered.map(order => `
    <tr>
      <td><input type="checkbox" class="order-check" /></td>
      <td><strong>${order.id}</strong></td>
      <td>${order.customer}<span class="muted">${order.email}</span></td>
      <td>${order.date}<span class="muted">${order.time}</span></td>
      <td><span class="payment-pill">${order.payment}</span></td>
      <td class="right"><strong>${money(order.total)}</strong></td>
      <td><span class="status ${orderStatusClass(order.status)}">${order.status}</span></td>
      <td class="right"><span class="action-cell"><button title="Lihat detail">⊙</button><button title="Ubah Status" onclick="openEditOrder('${order.id}', '${order.status}')">✎</button></span></td>
    </tr>
  `).join('') || `<tr><td colspan="8" style="text-align:center;color:#667085;padding:36px">Tidak ada pesanan yang sesuai.</td></tr>`;

  document.getElementById('orderCountText').textContent = `Showing ${filtered.length ? 1 : 0} to ${filtered.length} of ${orders.length} results`;
  renderActiveFilters(status, payment);
  bindRowSelections();
}

function renderActiveFilters(status, payment) {
  const target = document.getElementById('activeFilters');
  const chips = [];
  if (status !== 'all') chips.push(`Status: ${status}`);
  if (payment !== 'All Payments') chips.push(`Payment: ${payment}`);

  target.innerHTML = chips.length
    ? `<span>ACTIVE FILTERS:</span>${chips.map(chip => `<span class="filter-chip">${chip} ×</span>`).join('')}<button class="filter-clear" id="clearFilters">Clear All</button>`
    : '';

  const clear = document.getElementById('clearFilters');
  if (clear) clear.addEventListener('click', () => {
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('paymentFilter').value = 'All Payments';
    renderOrders();
  });
}

// Initial data load
document.addEventListener('DOMContentLoaded', () => {
  // Set dynamic header date
  const headerDateEl = document.getElementById('headerDate');
  if (headerDateEl) {
      const now = new Date();
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
      const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
      headerDateEl.innerHTML = `▣ 1–${lastDay} ${monthNames[now.getMonth()]} ${now.getFullYear()}`;
  }
  loadAdminData();
});

function bindRowSelections() {
  document.querySelectorAll('.order-check').forEach(input => {
    input.addEventListener('change', () => input.closest('tr').classList.toggle('selected', input.checked));
  });
}

function stockLabel(product) {
  if (product.status === 'low') return `<span class="stock-badge low">⚠ Low Stock: ${product.stock}</span>`;
  if (product.status === 'out') return `<span class="stock-badge out">● Out of Stock</span>`;
  return `<span class="stock-badge in">● In Stock: ${product.stock}</span>`;
}

function renderProducts(filter = 'all') {
  const grid = document.getElementById('productsGrid');
  const q = document.getElementById('globalSearch').value.trim().toLowerCase();
  const filtered = products.filter(product => {
    const matchesFilter = filter === 'all' || product.category === filter;
    const matchesSearch = !q || [product.name, product.category, product.sku].some(value => String(value).toLowerCase().includes(q));
    return matchesFilter && matchesSearch;
  });

  grid.innerHTML = filtered.map(product => `
    <article class="product-card">
      <div class="product-image">
        <img src="${product.image}" alt="${product.name}" loading="lazy" />
        ${stockLabel(product)}
      </div>
      <div class="product-body">
        <div class="product-meta"><span>${product.category}</span><span>SKU: ${product.sku}</span></div>
        <h3>${product.name}</h3>
        <div class="product-bottom"><b>${money(product.price)}</b><button class="edit-btn" title="Edit produk">⌁</button></div>
      </div>
    </article>
  `).join('') || `<p style="color:#667085">Produk tidak ditemukan.</p>`;
}

function renderCustomers(filter = 'all') {
  const grid = document.getElementById('customersGrid');
  const q = document.getElementById('customerSearch').value.trim().toLowerCase();
  const filtered = customers.filter(customer => {
    const matchesFilter = filter === 'all' || customer.type === filter;
    const matchesSearch = !q || [customer.name, customer.email, customer.tag, customer.type].some(value => value.toLowerCase().includes(q));
    return matchesFilter && matchesSearch;
  });

  grid.innerHTML = filtered.map(customer => `
    <article class="customer-card">
      <div class="customer-top">
        <div class="customer-main">
          <div class="avatar ${customer.avatarClass}">${customer.avatar}</div>
          <div><h3>${customer.name}</h3><span class="customer-tag">${customer.tag}</span></div>
        </div>
        <button class="icon-btn">⋮</button>
      </div>
      <div class="customer-lines">
        <span>✉ ${customer.email}</span>
        <span>♧ ${customer.detail}</span>
      </div>
      <div class="customer-stats">
        <div><span>Total Pesanan</span><b>${customer.orders}</b></div>
        <div><span>Login Terakhir</span><b>${customer.lastLogin}</b></div>
      </div>
    </article>
  `).join('') || `<p style="color:#667085">Pelanggan tidak ditemukan.</p>`;
}

function csvDownload(filename, rows) {
  const csv = rows.map(row => row.map(value => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('open');
}
function closeModals() {
  document.querySelectorAll('.modal').forEach(modal => modal.classList.remove('open'));
}

document.querySelectorAll('[data-modal]').forEach(button => button.addEventListener('click', () => openModal(button.dataset.modal)));
document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', closeModals));
document.querySelectorAll('.modal').forEach(modal => modal.addEventListener('click', event => {
  if (event.target === modal) closeModals();
}));

document.getElementById('orderSearch').addEventListener('input', renderOrders);
document.getElementById('statusFilter').addEventListener('change', renderOrders);
document.getElementById('paymentFilter').addEventListener('change', renderOrders);
document.getElementById('chartRange').addEventListener('change', (e) => {
    loadAdminData(e.target.value);
});

document.getElementById('dateFilter').addEventListener('change', () => showToast('Filter tanggal demo aktif.'));

// Event delegation for dynamically created product filter tabs
const tabsContainer = document.getElementById('productCategoryTabs');
if (tabsContainer) {
  tabsContainer.addEventListener('click', (event) => {
    if (event.target.tagName === 'BUTTON' && event.target.dataset.productFilter) {
      document.querySelectorAll('[data-product-filter]').forEach(item => item.classList.remove('active'));
      event.target.classList.add('active');
      renderProducts(event.target.dataset.productFilter);
    }
  });
}

document.querySelectorAll('[data-customer-filter]').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('[data-customer-filter]').forEach(item => item.classList.remove('active'));
    tab.classList.add('active');
    renderCustomers(tab.dataset.customerFilter);
  });
});

document.getElementById('customerSearch').addEventListener('input', () => {
  const active = document.querySelector('[data-customer-filter].active').dataset.customerFilter;
  renderCustomers(active);
});

document.getElementById('globalSearch').addEventListener('input', (event) => {
  const value = event.target.value.trim().toLowerCase();
  if (!value) {
    renderProducts(document.querySelector('[data-product-filter].active').dataset.productFilter);
    return;
  }
  const matchingPage = document.querySelector('.page.active')?.id;
  if (matchingPage === 'page-products') renderProducts(document.querySelector('[data-product-filter].active').dataset.productFilter);
  if (matchingPage === 'page-orders') {
    document.getElementById('orderSearch').value = value;
    renderOrders();
  }
  if (matchingPage === 'page-customers') {
    document.getElementById('customerSearch').value = value;
    renderCustomers(document.querySelector('[data-customer-filter].active').dataset.customerFilter);
  }
});

document.getElementById('selectAllOrders').addEventListener('change', (event) => {
  document.querySelectorAll('.order-check').forEach(check => {
    check.checked = event.target.checked;
    check.closest('tr').classList.toggle('selected', check.checked);
  });
});

document.getElementById('exportReport').addEventListener('click', () => {
  csvDownload('harmoni-orders-report.csv', [
    ['Order ID', 'Customer', 'Email', 'Date', 'Payment', 'Total', 'Status'],
    ...orders.map(order => [order.id, order.customer, order.email, `${order.date} ${order.time}`, order.payment, order.total, order.status])
  ]);
  showToast('Report CSV berhasil dibuat.');
});

document.getElementById('printShipping').addEventListener('click', () => {
  showToast('Mode cetak surat pengiriman dibuka.');
  window.print();
});

document.getElementById('productForm').addEventListener('submit', (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const formData = new FormData(form);
  const btn = form.querySelector('button[type="submit"]');
  btn.textContent = 'Menyimpan...';
  btn.disabled = true;

  fetch('add_product.php', {
      method: 'POST',
      body: formData
  })
  .then(res => res.json())
  .then(data => {
      if(data.error) {
          showToast(data.error);
      } else {
          showToast('Produk berhasil ditambahkan!');
          form.reset();
          closeModals();
          loadAdminData(); // Refresh data
      }
  })
  .catch(err => {
      console.error(err);
      showToast('Gagal menghubungi server.');
  })
  .finally(() => {
      btn.textContent = 'Simpan Produk';
      btn.disabled = false;
  });
});

function openEditOrder(id, status) {
    document.getElementById('updateOrderIdDisplay').textContent = id;
    document.getElementById('updateOrderId').value = id;
    
    const select = document.getElementById('updateOrderStatus');
    for(let i=0; i<select.options.length; i++) {
        if(select.options[i].value.toLowerCase() === status.toLowerCase()) {
            select.selectedIndex = i;
            break;
        }
    }
    openModal('updateOrderModal');
}

document.getElementById('updateOrderForm').addEventListener('submit', (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const formData = new FormData(form);
  const btn = form.querySelector('button[type="submit"]');
  btn.textContent = 'Menyimpan...';
  btn.disabled = true;

  fetch('update_order.php', {
      method: 'POST',
      body: formData
  })
  .then(res => res.json())
  .then(data => {
      if(data.error) {
          showToast(data.error);
      } else {
          showToast('Status pesanan diperbarui!');
          closeModals();
          loadAdminData(); // Refresh data
      }
  })
  .catch(err => {
      console.error(err);
      showToast('Gagal menghubungi server.');
  })
  .finally(() => {
      btn.textContent = 'Simpan Perubahan';
      btn.disabled = false;
  });
});

document.getElementById('loadMoreProducts').addEventListener('click', () => showToast('Semua produk sudah ditampilkan.'));
document.getElementById('loadMoreCustomers').addEventListener('click', () => showToast('Semua pelanggan sudah ditampilkan.'));

const style = document.createElement('style');
style.textContent = `.mini-avatar{width:28px;height:28px;border-radius:999px;background:#eef2f6;color:#344054;display:inline-grid;place-items:center;font-size:10px;font-weight:900}`;
document.head.appendChild(style);

function drawChart(chartData) {
    if (!chartData || chartData.length === 0) return;
    const maxTotal = Math.max(...chartData.map(d => d.total));
    const maxVal = maxTotal > 0 ? maxTotal * 1.2 : 1000;

    const startX = 50;
    const xSpacing = (740 - startX * 2) / Math.max(1, chartData.length - 1);
    const minY = 40;
    const maxY = 220;
    const h = maxY - minY;

    let points = [];
    let dotsHtml = '';
    let labelsHtml = '';

    chartData.forEach((d, i) => {
        const x = startX + (i * xSpacing);
        const y = maxY - ((d.total / maxVal) * h);
        points.push({x, y});

        dotsHtml += `<circle cx="${x}" cy="${y}" r="5"><title>${d.month}: ${money(d.total)}</title></circle>`;
        labelsHtml += `<text x="${x}" y="245" text-anchor="middle">${d.month}</text>`;
    });

    let pathD = `M${points[0].x} ${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
        pathD += ` L${points[i].x} ${points[i].y}`;
    }

    const areaD = `${pathD} L${points[points.length-1].x} ${maxY} L${points[0].x} ${maxY} Z`;

    document.getElementById('chartLine').setAttribute('d', pathD);
    document.getElementById('chartArea').setAttribute('d', areaD);
    document.getElementById('chartDots').innerHTML = dotsHtml;
    document.getElementById('chartLabels').innerHTML = labelsHtml;
}

function loadAdminData(filter = 'year') {
    fetch('get_admin_data.php?filter=' + filter)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                showToast(data.error);
                return;
            }
            orders = data.orders;
            products = data.products;
            customers = data.customers;

            // Update stats
            const statRevMonth = document.getElementById('statRevenueMonth');
            const statRevAll = document.getElementById('statRevenueAllTime');
            const statRevGrowth = document.getElementById('statRevenueGrowth');
            
            if (statRevMonth) statRevMonth.textContent = money(data.stats.thisMonthRevenue);
            if (statRevAll) statRevAll.textContent = 'Total: ' + money(data.stats.totalRevenue);
            
            if (statRevGrowth) {
                const thisM = data.stats.thisMonthRevenue;
                const lastM = data.stats.lastMonthRevenue;
                let growthTxt = '';
                let growthClass = 'neutral';
                
                if (lastM === 0 && thisM > 0) {
                    growthTxt = '↗ +100% vs Bulan lalu';
                    growthClass = 'positive';
                } else if (lastM === 0 && thisM === 0) {
                    growthTxt = '→ 0% vs Bulan lalu';
                } else {
                    const diff = ((thisM - lastM) / lastM) * 100;
                    if (diff > 0) {
                        growthTxt = `↗ +${diff.toFixed(1)}% vs Bulan lalu`;
                        growthClass = 'positive';
                    } else if (diff < 0) {
                        growthTxt = `↘ ${diff.toFixed(1)}% vs Bulan lalu`;
                        growthClass = 'negative';
                    } else {
                        growthTxt = `→ 0% vs Bulan lalu`;
                    }
                }
                statRevGrowth.textContent = growthTxt;
                statRevGrowth.className = growthClass;
            }

            document.querySelector('.stat-card:nth-child(2) strong').textContent = data.stats.totalOrders;
            document.querySelector('.stat-card:nth-child(3) strong').textContent = data.stats.totalCustomers;

            drawChart(data.chart);
            
            // Populate category select and tabs
            if (data.categories) {
                const catSelect = document.querySelector('select[name="category"]');
                if (catSelect) {
                    catSelect.innerHTML = '<option value="">Pilih Kategori</option>' + data.categories.map(c => `<option value="${c.id_kategori}">${c.nama_kategori}</option>`).join('');
                }
                
                const catTabs = document.getElementById('productCategoryTabs');
                if (catTabs) {
                    // Keep "All" and "+ Tambah Produk" buttons, insert categories in between
                    const allBtn = '<button class="tab active" data-product-filter="all">All</button>';
                    const addBtn = '<button class="primary-btn" data-modal="productModal">+ Tambah Produk</button>';
                    const dynamicTabs = data.categories.map(c => {
                        const capitalized = c.nama_kategori.charAt(0).toUpperCase() + c.nama_kategori.slice(1);
                        return `<button class="tab" data-product-filter="${capitalized}">${capitalized}</button>`;
                    }).join('');
                    catTabs.innerHTML = allBtn + dynamicTabs + addBtn;
                    
                    // Re-bind modal events for the dynamically created Add Product button
                    document.querySelectorAll('[data-modal="productModal"]').forEach(button => {
                        // Remove old listener to avoid duplicates if possible, or just add
                        button.addEventListener('click', () => openModal('productModal'));
                    });
                }
            }

            renderBestProducts();
            renderRecentOrders();
            renderOrders();
            renderProducts();
            renderCustomers();
        })
        .catch(err => {
            console.error('Failed to load admin data:', err);
            showToast('Error: ' + err.message);
        });
}

loadAdminData();
