export default function ListFilterBar({ search, onSearch, placeholder = 'Cari data...', filter, onFilter, options = [], label = 'Semua kategori' }) {
  return <div className="mb-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_220px]">
    <label className="relative block"><span className="sr-only">{placeholder}</span><span aria-hidden="true" className="material-symbol absolute left-3 top-1/2 -translate-y-1/2 text-muted">search</span><input type="search" value={search} onChange={event => onSearch(event.target.value)} placeholder={placeholder} className="h-11 w-full rounded-xl border border-outline/50 bg-white pl-10 pr-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15" /></label>
    {options.length > 0 && <label><span className="sr-only">{label}</span><select value={filter} onChange={event => onFilter(event.target.value)} className="h-11 w-full rounded-xl border border-outline/50 bg-white px-3 text-sm outline-none focus:border-primary">{options.map(option => <option key={option.value} value={option.value}>{option.label}</option>)}</select></label>}
  </div>;
}
