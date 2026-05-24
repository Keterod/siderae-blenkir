export default function AppLayout({ sidebar, header, children, mainClassName }) {
  return (
    <div className="flex h-screen overflow-hidden bg-background text-foreground">
      {sidebar}
      <div className="flex min-h-0 min-w-0 flex-1 flex-col">
        {header}
        <main
          id="main-content"
          className={
            mainClassName
            ?? 'min-h-0 flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-10 lg:py-8'
          }
        >
          {children}
        </main>
      </div>
    </div>
  );
}
