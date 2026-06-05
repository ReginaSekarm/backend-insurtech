public function up()
{
    Schema::table('produk', function (Blueprint $table) {
        $table->string('file_snk')->nullable();
    });
}

public function down()
{
    Schema::table('produk', function (Blueprint $table) {
        $table->dropColumn('file_snk');
    });
}