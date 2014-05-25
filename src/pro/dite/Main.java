package pro.dite;

import com.sun.tools.javac.util.Pair;
import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.lib.Repository;
import org.eclipse.jgit.revwalk.RevCommit;
import org.eclipse.jgit.storage.file.FileRepositoryBuilder;

import java.io.File;
import java.io.IOException;
import java.util.HashSet;
import java.util.Hashtable;

public class Main
{

    public static void main(String[] args) throws IOException, GitAPIException
    {
        // TODO do not require being in root
        String gitDir = System.getProperty("user.dir") + "/.git";
        FileRepositoryBuilder builder = new FileRepositoryBuilder();
        Repository repo = builder.setGitDir(new File(gitDir))
                .readEnvironment()
                .findGitDir()
                .build();

        final Hashtable<String, HashSet<String>> index = new Hashtable<String, HashSet<String>>();

        final File cacheFile = new File(gitDir + "/phpgit.bin");
        final Cache cache = Cache.loadFromFile(cacheFile);

        File repoDir = new File(gitDir);

        final CacheEntry e = cache.entries.get("commit a0c7ebdb175fc115fac967b1754d3f1033a015b7 1393330398 ----sp");

        HeadWalker walker = new HeadWalker(repo)
        {
            @Override
            protected void onCommitDone(RevCommit commit, boolean skip)
            {
                CacheEntry entry = cache.entries.get(commit.getId().toString());
                if (entry != null)
                {
                    if (!skip)
                    {
                        entry.compile();
                    }
                    for (String def : entry.index)
                    {
                        index.putIfAbsent(def, new HashSet<String>());
                        index.get(def).add(entry.author);
                    }
                }
            }

            @Override
            protected boolean shouldSkipCommit(RevCommit commit)
            {
                return cache.contains(commit);
            }

            @Override
            public void processFileDiff(RevCommit commit, EditList edits, PhpFile a, PhpFile b)
            {
                CacheEntry entry = cache.entries.get(commit.getId().toString());
                if (entry == null) {
                    entry = new CacheEntry(commit);
                }
                for (Edit edit : edits)
                {
                    System.out.println("\t\t"+edit);
                    if (edit.getType() == Edit.Type.DELETE)
                    {
                        HashSet<String> defs = processEdit(a, edit.getBeginA(), edit.getEndA(), commit);
                        entry.index.addAll(defs);
                        for (String def : defs)
                        {
                            if (!isDefOutsideOf(def, a, edit.getBeginA(), edit.getEndA()))
                            {
                                entry.removed.add(def);
                            }
                        }
                    }

                    if (edit.getType() == Edit.Type.REPLACE)
                    {
                        HashSet<String> changesA = processEdit(a, edit.getBeginA(), edit.getEndA(), commit);
                        HashSet<String> changesB = processEdit(b, edit.getBeginB(), edit.getEndB(), commit);
                        entry.index.addAll(changesA);
                        entry.index.addAll(changesB);

                        HashSet<String> removed = new HashSet<String>();
                        HashSet<String> added = new HashSet<String>();
                        for (String rem : changesA)
                        {
                            if (!changesB.contains(rem))
                            {
                                removed.add(rem);
                            }
                        }
                        for (String add : changesB)
                        {
                            if (!changesA.contains(add))
                            {
                                added.add(add);
                            }
                        }


                        if (removed.size() == 1 && added.size() == 1)
                        {
                            String from = (String) removed.toArray()[0];
                            String to = (String) added.toArray()[0];
                            entry.renamed.add(new Pair<String, String>(from, to));
                        }
                        else
                        {
                            for (String def : removed)
                            {
                                if (!isDefOutsideOf(def, a, edit.getBeginA(), edit.getEndA()))
                                {
                                    entry.removed.add(def);
                                }
                            }
                            for (String def : added)
                            {
                                if (!isDefOutsideOf(def, b, edit.getBeginB(), edit.getEndB()))
                                {
                                    entry.added.add(def);
                                }
                            }
                        }
                    }

                    if (edit.getType() == Edit.Type.INSERT)
                    {
                        HashSet<String> defs = processEdit(b, edit.getBeginB(), edit.getEndB(), commit);
                        entry.index.addAll(defs);

                        for (String def : defs)
                        {
                            if (!isDefOutsideOf(def, b, edit.getBeginB(), edit.getEndB()))
                            {
                                entry.added.add(def);
                            }
                        }
                    }
                }
                cache.entries.put(commit.getId().toString(), entry);
            }

            private boolean isDefOutsideOf(String def, PhpFile php, int begin, int end)
            {
                for (int i = 0; i < begin; ++i)
                {
                    if (php.lines.get(i).toString().equals(def))
                    {
                        return true;
                    }
                }
                for (int i = end + 1; i < php.lines.size(); ++i)
                {
                    if (php.lines.get(i).toString().equals(def))
                    {
                        return true;
                    }
                }
                return false;
            }

            @Override
            public void processFileDiff(RevCommit commit, PhpFile b)
            {
                CacheEntry entry = new CacheEntry(commit);
                for (String def : processEdit(b, 0, b.lines.size(), commit))
                {
                    entry.index.add(def);
                }
                cache.entries.put(commit.getId().toString(), entry);
            }

            @Override
            public void processFileDiff(RevCommit commit)
            {
                // cache commit even if there were no changes the prevent further parsing
                cache.entries.put(commit.getId().toString(), new CacheEntry(commit));
            }
        };

        walker.walk();
        cache.saveToFile(cacheFile);

        // build index from cache from all commits hit

        if (args.length >= 1 && args[0].equals("log"))
        {
            LogFormatter log = new LogFormatter(repo);
            log.print(cache);
        }
        else if (args.length >= 1)
        {
            String def = args[0];
            System.out.println("Authors of: " + def);
            if (!index.containsKey(def))
            {
                System.out.println("definition not found");
                return;
            }
            HashSet<String> authors = index.get(def);
            for (String author : authors)
            {
                System.out.println("\t" + author);
            }
        }
    }

    private static HashSet<String> processEdit(PhpFile php, int begin, int end, RevCommit commit)
    {
        HashSet<String> list = new HashSet<String>();
        for (int i = begin; i < end; ++i)
        {
            PhpFile.Line line = php.lines.get(i);
            if (line.isFunction())
            {
                list.add(line.toStringFunction());
            }
            list.add(line.toString());
        }
        return list;
    }

}
